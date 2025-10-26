<?php

declare(strict_types=1);

namespace App\Services\Discounts;

use Illuminate\Contracts\Session\Session;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class DiscountContextBuilder
{
    public function __construct(private readonly Session $session) {}

    /**
     * @return array<string, mixed>
     */
    public function fromRequest(Request $request, ?string $code = null): array
    {
        $cart = (array) $request->input('cart', []);
        $shipping = $request->input('shipping.base_amount');

        return $this->build(
            $request->user(),
            $code,
            $cart,
            $shipping !== null ? (float) $shipping : null,
        );
    }

    /**
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return array<string, mixed>
     */
    public function fromSession($user, ?string $code = null): array
    {
        return $this->build($user, $code, []);
    }

    /**
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @param  array<string, mixed>                            $cartPayload
     * @return array<string, mixed>
     */
    private function build($user, ?string $code, array $cartPayload, ?float $shippingBase = null): array
    {
        $items = [];
        $subtotal = 0.0;

        if ($cartPayload !== []) {
            $items = array_values(array_map(function ($item): array {
                $item = (array) $item;

                return [
                    'product_id' => isset($item['product_id']) ? (int) $item['product_id'] : null,
                    'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                    'quantity'   => isset($item['quantity']) ? max(0, (int) $item['quantity']) : 0,
                    'unit_price' => isset($item['unit_price']) ? (float) $item['unit_price'] : 0.0,
                ];
            }, (array) Arr::get($cartPayload, 'items', [])));
            $subtotal = (float) Arr::get($cartPayload, 'subtotal', 0.0);
            if ($subtotal <= 0.0 && $items !== []) {
                $subtotal = collect($items)->sum(fn (array $item): float => $item['unit_price'] * max(0, $item['quantity']));
            }
        } else {
            if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
                try {
                    foreach (\Darryldecode\Cart\Facades\CartFacade::session($this->session->getId())->getContent() as $item) {
                        $items[] = [
                            'product_id' => optional($item->associatedModel)->id,
                            'variant_id' => method_exists($item->associatedModel, 'getKey') ? $item->associatedModel->getKey() : null,
                            'quantity'   => (int) $item->quantity,
                            'unit_price' => (float) $item->price,
                        ];
                    }
                    $subtotal = (float) \Darryldecode\Cart\Facades\CartFacade::session($this->session->getId())->getSubTotal();
                } catch (Throwable $e) {
                    $items = [];
                    $subtotal = 0.0;
                }
            }
        }

        $shippingBase ??= (float) Arr::get($cartPayload, 'shipping.base_amount', data_get($this->session->get('checkout'), 'shipping_option.0.price', 0.0));

        $userId = $user?->getAuthIdentifier();
        $groupIds = $this->resolveGroupIds($userId);
        $partnerTier = $this->resolvePartnerTier($userId);

        return [
            'currency_code' => current_currency(),
            'channel_id'    => config('app.channel_id') ?? config('app.url'),
            'user_id'       => $userId,
            'group_ids'     => $groupIds,
            'partner_tier'  => $partnerTier,
            'now'           => now(),
            'code'          => $code ? mb_strtoupper(trim($code)) : null,
            'cart'          => [
                'subtotal' => $subtotal,
                'items'    => $items,
            ],
            'shipping' => [
                'base_amount' => $shippingBase,
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private function resolveGroupIds($userId): array
    {
        if (! $userId) {
            return [];
        }

        foreach (['customer_group_user', 'sh_customer_group_user'] as $table) {
            if (! $this->tableExists($table)) {
                continue;
            }

            try {
                $ids = DB::table($table)
                    ->where('user_id', $userId)
                    ->pluck('group_id')
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values()
                    ->all();

                if ($ids !== []) {
                    return $ids;
                }
            } catch (QueryException $e) {
                continue;
            }
        }

        return [];
    }

    private function resolvePartnerTier($userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $candidates = [
            ['partner_users', 'partners'],
            ['sh_partner_users', 'sh_partners'],
        ];

        foreach ($candidates as [$pivot, $table]) {
            if (! $this->tableExists($pivot) || ! $this->tableExists($table)) {
                continue;
            }

            try {
                $tier = DB::table($pivot . ' as pu')
                    ->join($table . ' as p', 'p.id', '=', 'pu.partner_id')
                    ->where('pu.user_id', $userId)
                    ->value('p.tier');

                if ($tier !== null) {
                    return (string) $tier;
                }
            } catch (QueryException $e) {
                continue;
            }
        }

        return null;
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable $e) {
            return false;
        }
    }
}
