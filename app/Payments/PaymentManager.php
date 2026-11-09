<?php

namespace App\Payments;

/**
 * سجلّ طرق الدفع — المرحلة 6C.
 *
 * في 6C مسجّل COD بس. لإضافة الأونلاين في 6E:
 *   app(PaymentManager::class)->register(new OnlineGateway());
 * من غير أي تغيير في الـ checkout.
 */
class PaymentManager
{
    /** @var array<string, PaymentMethod> */
    protected array $methods = [];

    protected ?string $default = null;

    public function register(PaymentMethod $method, bool $default = false): static
    {
        $this->methods[$method->key()] = $method;

        if ($default || $this->default === null) {
            $this->default = $method->key();
        }

        return $this;
    }

    /** @return array<string, PaymentMethod> */
    public function available(): array
    {
        return $this->methods;
    }

    public function get(string $key): ?PaymentMethod
    {
        return $this->methods[$key] ?? null;
    }

    public function default(): ?PaymentMethod
    {
        return $this->default ? $this->get($this->default) : null;
    }

    public function has(string $key): bool
    {
        return isset($this->methods[$key]);
    }
}
