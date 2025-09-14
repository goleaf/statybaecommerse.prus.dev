<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;
/**
 * CustomerGroupController
 * 
 * HTTP controller handling CustomerGroupController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class CustomerGroupController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = CustomerGroup::query()->where('is_enabled', true)->with(['users', 'discounts', 'priceLists']);
        // Filter by discount percentage if requested
        if ($request->has('min_discount')) {
            $query->where('discount_percentage', '>=', $request->get('min_discount'));
        }
        if ($request->has('max_discount')) {
            $query->where('discount_percentage', '<=', $request->get('max_discount'));
        }
        // Search by name
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '\$.lt') LIKE ?", ["%{$search}%"])->orWhereRaw("JSON_EXTRACT(name, '\$.en') LIKE ?", ["%{$search}%"]);
            });
        }
        $customerGroups = $query->paginate(12);
        return view('customer-groups.index', compact('customerGroups'));
    }
    /**
     * Display the specified resource with related data.
     * @param CustomerGroup $customerGroup
     * @return View
     */
    public function show(CustomerGroup $customerGroup): View
    {
        // Only show enabled customer groups
        if (!$customerGroup->is_enabled) {
            abort(404);
        }
        $customerGroup->load(['users', 'discounts', 'priceLists']);
        return view('customer-groups.show', compact('customerGroup'));
    }
    /**
     * Handle join functionality with proper error handling.
     * @param Request $request
     * @param CustomerGroup $customerGroup
     */
    public function join(Request $request, CustomerGroup $customerGroup)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', __('customer_groups.login_required'));
        }
        if (!$customerGroup->is_enabled) {
            return redirect()->back()->with('error', __('customer_groups.group_not_available'));
        }
        // Check if user is already in the group
        if ($customerGroup->users()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('info', __('customer_groups.already_member'));
        }
        // Attach user to customer group
        $customerGroup->users()->attach($user->id);
        return redirect()->back()->with('success', __('customer_groups.joined_successfully'));
    }
    /**
     * Handle leave functionality with proper error handling.
     * @param Request $request
     * @param CustomerGroup $customerGroup
     */
    public function leave(Request $request, CustomerGroup $customerGroup)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', __('customer_groups.login_required'));
        }
        // Check if user is in the group
        if (!$customerGroup->users()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('error', __('customer_groups.not_member'));
        }
        // Detach user from customer group
        $customerGroup->users()->detach($user->id);
        return redirect()->back()->with('success', __('customer_groups.left_successfully'));
    }
}