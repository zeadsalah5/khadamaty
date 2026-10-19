<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Support\Store\OrderNotifier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * لوحة إدارة الطلبات (الدفع عند الاستلام) — المرحلة 6C.
 *
 * تغيير الحالة بيعدّي دايماً على Order::changeStatus() عشان أثر المخزون
 * (رجوع عند الإلغاء/المرتجع، والمستعمل يتعلّم «مباع» عند التوصيل) يتطبّق مرة واحدة.
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'الطلبات';

    protected static ?string $navigationLabel = 'طلبات المتجر';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'طلبات المتجر';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::query()->where('status', 'new')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الطلب')->schema([
                Forms\Components\TextInput::make('order_number')->label('رقم الطلب')->disabled(),
                Forms\Components\TextInput::make('created_at')->label('التاريخ')->disabled()
                    ->formatStateUsing(fn ($state) => optional($state)->format('Y-m-d H:i')),
                Forms\Components\TextInput::make('customer_name')->label('العميل')->disabled(),
                Forms\Components\TextInput::make('customer_phone')->label('الموبايل')->disabled(),
                Forms\Components\TextInput::make('area_name')->label('المنطقة')->disabled(),
                Forms\Components\TextInput::make('payment_method')->label('طريقة الدفع')->disabled()
                    ->formatStateUsing(fn ($state) => static::paymentMethodLabel($state)),
                Forms\Components\TextInput::make('payment_status')->label('حالة الدفع')->disabled()
                    ->formatStateUsing(fn ($state) => Order::PAYMENT_STATUSES[$state] ?? $state),
                Forms\Components\Textarea::make('address')->label('العنوان')->disabled()->rows(2)->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label('ملاحظات العميل')->disabled()->rows(2)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('المنتجات')->schema([
                Forms\Components\Placeholder::make('items_list')->hiddenLabel()
                    ->content(fn (?Order $record) => $record ? static::itemsHtml($record) : '')
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('سجلّ محاولات الدفع')
                ->visible(fn (?Order $record) => $record && $record->paymentAttempts()->exists())
                ->schema([
                    Forms\Components\Placeholder::make('attempts_list')->hiddenLabel()
                        ->content(fn (?Order $record) => $record ? static::attemptsHtml($record) : '')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('الحالة والإدارة')->schema([
                Forms\Components\Select::make('status')->label('حالة الطلب')
                    ->options(Order::STATUSES)->required()->native(false)
                    ->helperText('الإلغاء/المرتجع بيرجّع المخزون، و«تم التوصيل» بيعلّم المستعمل كمباع.'),
                Forms\Components\Textarea::make('admin_notes')->label('ملاحظات داخلية (مش بتظهر للعميل)')
                    ->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    /** جدول بنود الطلب كـ HTML للعرض في الفورم. */
    protected static function itemsHtml(Order $record): HtmlString
    {
        $record->loadMissing('items');

        $rows = $record->items->map(function ($item) {
            $cond = $item->condition === 'used' ? 'مستعمل' : 'جديد';

            return '<tr>'
                . '<td style="padding:6px 8px;">' . e($item->name) . ' <span style="color:#888;">(' . $cond . ')</span></td>'
                . '<td style="padding:6px 8px;text-align:center;">' . $item->qty . ' × ' . number_format((float) $item->unit_price) . '</td>'
                . '<td style="padding:6px 8px;text-align:left;font-weight:bold;">' . number_format((float) $item->line_total) . ' ج.م</td>'
                . '</tr>';
        })->implode('');

        $totals = '<tr><td colspan="2" style="padding:6px 8px;text-align:left;color:#888;">المنتجات</td><td style="padding:6px 8px;text-align:left;">' . number_format((float) $record->subtotal) . ' ج.م</td></tr>'
            . '<tr><td colspan="2" style="padding:6px 8px;text-align:left;color:#888;">التوصيل</td><td style="padding:6px 8px;text-align:left;">' . number_format((float) $record->delivery_fee) . ' ج.م</td></tr>'
            . '<tr><td colspan="2" style="padding:6px 8px;text-align:left;font-weight:bold;">الإجمالي</td><td style="padding:6px 8px;text-align:left;font-weight:bold;color:#E5171F;">' . number_format((float) $record->total) . ' ج.م</td></tr>';

        return new HtmlString('<table style="width:100%;border-collapse:collapse;font-size:14px;">' . $rows . $totals . '</table>');
    }

    /** اسم طريقة الدفع للعرض. */
    protected static function paymentMethodLabel(?string $key): string
    {
        return match ($key) {
            'cod'    => 'الدفع عند الاستلام',
            'paymob' => 'أونلاين (Paymob)',
            default  => (string) $key,
        };
    }

    /** جدول محاولات الدفع كـ HTML للعرض في الفورم. */
    protected static function attemptsHtml(Order $record): HtmlString
    {
        $rows = $record->paymentAttempts->map(function ($a) {
            $sig = $a->hmac_valid ? '✔' : '✖ توقيع غلط';
            $txn = $a->gateway_txn_id ? e($a->gateway_txn_id) : '—';

            return '<tr>'
                . '<td style="padding:6px 8px;">' . optional($a->created_at)->format('Y-m-d H:i') . '</td>'
                . '<td style="padding:6px 8px;">' . e($a->statusLabel()) . '</td>'
                . '<td style="padding:6px 8px;">' . ($a->amount !== null ? number_format((float) $a->amount) . ' ج.م' : '—') . '</td>'
                . '<td style="padding:6px 8px;direction:ltr;text-align:left;">' . $txn . '</td>'
                . '<td style="padding:6px 8px;">' . $sig . '</td>'
                . '</tr>';
        })->implode('');

        $head = '<tr style="color:#888;"><th style="padding:6px 8px;text-align:right;">التاريخ</th><th style="padding:6px 8px;text-align:right;">الحالة</th><th style="padding:6px 8px;text-align:right;">المبلغ</th><th style="padding:6px 8px;text-align:right;">معرّف المعاملة</th><th style="padding:6px 8px;text-align:right;">التوقيع</th></tr>';

        return new HtmlString('<table style="width:100%;border-collapse:collapse;font-size:13px;">' . $head . $rows . '</table>');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('رقم الطلب')->searchable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('customer_name')->label('العميل')->searchable()
                    ->description(fn (Order $record) => $record->customer_id ? 'حساب مسجّل' : 'طلب كضيف'),
                Tables\Columns\TextColumn::make('customer_phone')->label('الموبايل')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('area_name')->label('المنطقة')->searchable(),
                Tables\Columns\TextColumn::make('total')->label('الإجمالي')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->label('الدفع')->badge()
                    ->formatStateUsing(fn ($state) => Order::PAYMENT_STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        'pending_payment' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\SelectColumn::make('status')->label('الحالة')
                    ->options(Order::STATUSES)->selectablePlaceholder(false)
                    ->updateStateUsing(fn (Order $record, $state) => $record->changeStatus($state)),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options(Order::STATUSES),
                Tables\Filters\SelectFilter::make('payment_status')->label('حالة الدفع')->options(Order::PAYMENT_STATUSES),
                Tables\Filters\SelectFilter::make('area_name')->label('المنطقة')
                    ->options(fn () => Order::query()->distinct()->pluck('area_name', 'area_name')->filter()->all()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('تصدير Excel (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => static::exportCsv()),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Order $record) => OrderNotifier::ownerWhatsappLink($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Order $record) => OrderNotifier::ownerWhatsappLink($record) !== null),
                Tables\Actions\Action::make('refund')
                    ->label('تعليم كمُسترجَع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('تعليم الطلب كمُسترجَع')
                    ->modalDescription('ده بيعلّم حالة الدفع «مُسترجَع» ويسجّل العملية. الاسترجاع الفعلي للفلوس بيتعمل من لوحة البوابة. لإرجاع المخزون غيّر حالة الطلب لـ«مرتجع».')
                    ->visible(fn (Order $record) => $record->payment_status === 'paid')
                    ->action(function (Order $record) {
                        $record->payment_status = 'refunded';
                        $record->save();
                        $record->paymentAttempts()->create([
                            'gateway'        => $record->payment_method,
                            'gateway_txn_id' => null,
                            'status'         => 'refunded',
                            'amount'         => $record->total,
                            'hmac_valid'     => true, // إجراء يدوي من الأدمن
                            'payload'        => ['manual' => true, 'by' => auth()->id()],
                        ]);
                    }),
                Tables\Actions\EditAction::make()->label('عرض/تعديل')
                    ->using(function (Order $record, array $data): Order {
                        $newStatus = $data['status'] ?? $record->status;

                        if ($newStatus !== $record->status) {
                            $record->changeStatus($newStatus); // بيدير أثر المخزون + بيحفظ الحالة
                        }

                        $record->update(['admin_notes' => $data['admin_notes'] ?? null]);

                        return $record;
                    }),
            ]);
    }

    /** تصدير الطلبات كـ CSV (بـ BOM عشان Excel يقرا العربي صح). */
    protected static function exportCsv(): StreamedResponse
    {
        $headers = ['رقم الطلب', 'التاريخ', 'العميل', 'الموبايل', 'المنطقة', 'العنوان', 'المنتجات', 'التوصيل', 'الإجمالي', 'الحالة', 'طريقة الدفع', 'حالة الدفع'];

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, $headers);

            Order::query()->with('items')->orderByDesc('created_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $order) {
                    $items = $order->items->map(fn ($i) => $i->qty . '× ' . $i->name)->implode(' | ');

                    fputcsv($out, [
                        $order->order_number,
                        optional($order->created_at)->format('Y-m-d H:i'),
                        $order->customer_name, $order->customer_phone, $order->area_name, $order->address,
                        $items,
                        (float) $order->delivery_fee, (float) $order->total,
                        Order::STATUSES[$order->status] ?? $order->status,
                        static::paymentMethodLabel($order->payment_method),
                        Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status,
                    ]);
                }
            });

            fclose($out);
        }, 'orders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageOrders::route('/')];
    }
}
