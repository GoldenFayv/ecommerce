<?php

namespace App\Http\Controllers\ControlPanel\ApiV1;

use App\Http\Controllers\Controller;
use App\Models\admin\Admin;
use App\Models\admin\AdminRolePermission;
use App\Models\product\Product;
use App\Models\sale\Order;
use App\Models\sale\Payment;
use App\Models\sale\Sale;
use App\Models\User;
use App\Services\cpanel\AdminService;
use App\Services\cpanel\OrderService;
use App\Services\ProductServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{

    public function index(Request $request, OrderService $orderService, ProductServices $productServices)
    {
        $request->validate([
            'start' => "date",
            "end" => "date"
        ]);

        $start_date = now()->startOfDay();
        $end_date = now()->endOfDay();
        if (!empty($request->query("start_date"))) {
            $start_date = (new Carbon($request->query("start_date")))->startOfDay();
        }
        if (!empty($request->query("end_date"))) {
            $end_date = (new Carbon($request->query("end_date")))->endOfDay();
        }

        $total_new_sales = AdminRolePermission::whereBetween("created_at", [$start_date, $end_date])->get();

        $order = Order::whereBetween("created_at", [$start_date, $end_date]);

        $total_new_sales = Sale::whereBetween("created_at", [$start_date, $end_date])->count();
        $total_new_users = User::whereBetween("created_at", [$start_date, $end_date])->count();

        $total_order_amount = $order->get()->sum("total");
        $total_payments = (int)Payment::whereBetween("created_at", [$start_date, $end_date])->sum("paid_amount");
        $remaining_products = (int)Product::sum("available_crates");
        $remaining_products_amount = number_format(DB::select("select sum(available_crates * crate_price) as total from products")[0]->total, 2);
        // select distinct result using the order_id and latest using created_at todo:
        $total_due = (int)max(0, $total_order_amount - $total_payments);

        $new_orders_id = $order->pluck('id');
        $new_products_id = Product::whereBetween("created_at", [$start_date, $end_date])->where("is_active", false)->latest()->limit(10)->pluck('id');
        $date = $start_date == now()->startOfDay() ? "Today" : (new Carbon($start_date))->format("d M Y");


        // get all the dues in a week, month, year
        $week_days = now()->startOfWeek()->daysUntil(now()->endOfWeek());
        $month_weeks = now()->startOfMonth()->weeksUntil(now()->endOfMonth());
        $year_months = now()->startOfYear()->monthsUntil(now()->endOfYear());
        $daily_data = [];
        list($daily_data['total_payments'], $daily_data['total_dues']) = array_map(function (Carbon $date) {
            $total_payments = Payment::whereBetween("created_at", [$date->startOfDay()->toDateTimeString(), $date->endOfDay()->toDateTimeString()])->sum("paid_amount");
            $total_orders = Order::whereBetween("created_at", [$date->startOfDay()->toDateTimeString(), $date->endOfDay()->toDateTimeString()])->sum("total");
            // $data = [];
            // $data['total_payments'][] = $total_payments;
            $dues = max($total_orders - $total_payments, 0);
            return [$total_payments, $dues];
        }, $week_days->toArray());

        $weekly_data = [];
        array_map(function (Carbon $date) {
            $total_payments = Payment::whereBetween("created_at", [$date->startOfWeek()->toDateTimeString(), $date->endOfWeek()->toDateTimeString()])->sum("paid_amount");
            $total_orders = Order::whereBetween("created_at", [$date->startOfWeek()->toDateTimeString(), $date->endOfWeek()->toDateTimeString()])->sum("total");
            $weekly_data['total_payments'] = $total_payments;
            $weekly_data['total_dues'] = max($total_orders - $total_payments, 0);
        }, $month_weeks->toArray());

        $yearly_data = [];
        array_map(function (Carbon $date) {
            $total_payments = Payment::whereBetween("created_at", [$date->startOfMonth()->toDateTimeString(), $date->endOfMonth()->toDateTimeString()])->sum("paid_amount");
            $total_orders = Order::whereBetween("created_at", [$date->startOfMonth()->toDateTimeString(), $date->endOfMonth()->toDateTimeString()])->sum("total");
            $yearly_data['total_payments'] = $total_payments;
            $yearly_data['total_dues'] = max($total_orders - $total_payments, 0);
        }, $year_months->toArray());

        return $this->successResponse($date . " Analysis", [
            "daily_data" => $daily_data,
            "weekly_data" => $weekly_data,
            "yearly_data" => $yearly_data,
            "total_new_sales" => $total_new_sales,
            "total_new_orders" => count($new_orders_id),
            "total_new_users" => $total_new_users,
            "remaining_products" => $remaining_products,
            "total_stock_amount" => $remaining_products_amount,
            "orders_amount" => $total_order_amount,
            "total_new_payments" => $total_payments,
            "total_new_due" => $total_due,
            "new_orders" => array_map(function ($id) use ($orderService) {
                return $orderService->get_order_detail($id);
            }, $new_orders_id->toArray()),
            "new_products" => array_map(function ($id) use ($productServices) {
                return $productServices->getProductDetail($id);
            }, $new_products_id->toArray())
        ]);
    }

    public function updateProfile(Request $request, AdminService $adminService)
    {
        /** @var Admin */
        $admin = Auth::guard("admin")->user();
        if (!empty($request['first_name']))
            $admin->first_name = $request['first_name'];

        if (!empty($request['last_name']))
            $admin->last_name = $request['last_name'];

        if (!empty($request['email']))
            $admin->email = $request['email'];

        if (!empty($request['password']))
            $admin->password = Hash::make($request['password']);

        if (!empty($request['profile_picture'])) {
            // delete old image
            $old_image = $admin->profile_picture;
            Storage::delete("uploads/profile-pictures/" . $old_image);
            $filename = $this->uploadFile($request['profile_picture'], 'profile-pictures');
            $admin->profile_picture = $filename;
        }
        $admin->save();
        return $this->successResponse("Profile Updated", $adminService->getProfileDetails());
    }
    public function getProfile(Request $request, AdminService $adminService)
    {


        return $this->successResponse("Profile Updated", $adminService->getProfileDetails());
    }
}
