<?php

namespace App\Repositories\Enduser;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function findByUuidForClient(string $uuid, int $clientId): ?Order
    {
        return Order::with([
            'items.product',
            'items.unit',
            'quotationRequest',
            'client.clientProfile',
        ])
            ->where('uuid', $uuid)
            ->where('client_id', $clientId)
            ->first();
    }

    public function paginateForClient(int $clientId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('client_id', $clientId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }
}
