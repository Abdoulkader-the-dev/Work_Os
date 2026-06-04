<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemDestroyRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function update (ItemUpdateRequest $request, Item $item) {
        $data = $request->validated();

        $item->update($data);

        return response()->json(['message' => 'Item updated', 'item' => $item], 200);

    }

    public function delete (ItemDestroyRequest $request, Item $item) {
        $item->delete();
        return response()->json(['message' => 'Item deleted'], 200);
    }
}
