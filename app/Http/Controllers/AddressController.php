<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\User;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Store a newly created address.
     */
    public function list()
{
    $addresses = UserAddress::where('user_id', Auth::id())->get();

    return response()->json([
        'success' => true,
        'addresses' => $addresses
    ]);
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'street_address' => 'required|string',
            'ward' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        // Nếu đặt làm mặc định, xóa mặc định của địa chỉ cũ
        if (!empty($validated['is_default'])) {
            UserAddress::where('user_id', Auth::id())
                ->update(['is_default' => false]);
        }

        $validated['user_id'] = Auth::id();
        $validated['is_default'] = $validated['is_default'] ?? false;

        UserAddress::create($validated);

        return redirect()->route('user.profile')
            ->with('success', 'Địa chỉ đã được thêm thành công!');
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, UserAddress $address)
    {
        // Kiểm tra quyền sở hữu
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'street_address' => 'required|string',
            'ward' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        // Nếu đặt làm mặc định, xóa mặc định của địa chỉ cũ
        if (!empty($validated['is_default'])) {
            UserAddress::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $validated['is_default'] = $validated['is_default'] ?? false;

        $address->update($validated);

        return redirect()->route('user.profile')
            ->with('success', 'Địa chỉ đã được cập nhật thành công!');
    }

    /**
     * Delete the specified address.
     */
    public function showEditAddressOrder($orderId)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($orderId);

        return redirect()
            ->route('user.orderDetail', $order->id)
            ->with('error', 'Vui lòng chọn địa chỉ trong form cập nhật.');
    }

    public function editAddressOrder(Request $request, $orderId)
    {
        $request->validate([
            'selected_address_id' => [
                'required',
                'exists:user_addresses,id,user_id,' . Auth::id(),
            ],
        ]);

        $order = Order::where('user_id', Auth::id())->findOrFail($orderId);

        $order->update([
            'user_address_id' => $request->selected_address_id
        ]);

        return redirect()
            ->back()
            ->with('success', 'Cập nhật địa chỉ thành công');
    }
    public function delete(UserAddress $address)
    {
        // Kiểm tra quyền sở hữu
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return redirect()->route('user.profile')
            ->with('success', 'Địa chỉ đã được xóa thành công!');
    }
}
