<?php
// app/Repositories/Interfaces/FeatureRepositoryInterface.php

namespace App\Repositories\Feature\Interfaces;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Collection;

interface FeatureRepositoryInterface
{
    public function index(): Collection;
    public function store(array $data): Feature;
    public function update(int $id, array $data): Feature;
    public function destroy(int $id): bool;
}
