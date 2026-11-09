<?php

namespace App\Filament\Pages\Concerns;

use App\Support\Settings;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

/**
 * منطق مشترك لصفحات الإعدادات في Filament:
 * - mount: تعبئة الفورم بقيم الإعدادات الحالية (من الداتابيز أو fallback config).
 * - save: حفظ كل مفتاح في مجموعته + إشعار نجاح (الكاش بيتمسح تلقائياً).
 * - canAccess: أدمن فقط.
 *
 * الصفحة لازم تعرّف: schemaComponents() و keyGroups() ([key => group]).
 */
trait ManagesSettings
{
    use InteractsWithForms;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $state = [];

        foreach (array_keys($this->keyGroups()) as $key) {
            $state[$key] = Settings::get($key);
        }

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->schemaComponents())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($this->keyGroups() as $key => $group) {
            Settings::set($key, $data[$key] ?? null, $group);
        }

        Notification::make()
            ->title('اتحفظت الإعدادات وظهرت على الموقع فوراً.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ التغييرات')
                ->submit('save'),
        ];
    }

    /** @return array<int, \Filament\Forms\Components\Component> */
    abstract protected function schemaComponents(): array;

    /** @return array<string, string> خريطة مفتاح الإعداد => المجموعة */
    abstract protected function keyGroups(): array;
}
