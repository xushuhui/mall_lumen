<?php

namespace App\Http\Controllers;

use App\Models\Coupons;
use App\Models\Store;
use App\Models\UserCoupons;

class CouponController extends Controller
{
    
    /**
     * @OA\Post(
     *     path="/api/coupon", summary="首页推荐优惠券",
     *     @OA\Response(response="200", description="{
    data: [
    {
    end_time: 2020-05-22 09:46:13,
    created_at: 2020-05-12T01:45:48.000000Z,
    coupon_name: test,
    store_id: 1,
    store: {
    id: 1,
    name: store1,
    store_address: 岳麓区
    }
    }
    ],
    }"),
     *  @OA\RequestBody(@OA\MediaType(mediaType="application/json",
     *    @OA\Schema(
     *      @OA\Property(property="latitude", type="float", description="经度"),
     *      @OA\Property(property="longitude", type="float", description="纬度"),
     *   @OA\Property(property="action", type="string", description="recommend:推荐,latest:最新,search:搜索,filter:筛选"),
     *  @OA\Property(property="type", type="int", description="商家类型，1餐馆"),
     *     @OA\Property(property="name", type="string", description="商家名称"),
     * ))
     *      )
     * )
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $action = request('action');
        switch ($action) {
            case "recommend":
                return $this->recommend();
                break;
            case "latest":
                return $this->latest();
                break;
            case "search":
                return $this->search();
                break;
            case "filter":
                return $this->filter();
                break;
        }
        
    }
    
    private function recommend()
    {
        $coupons = Coupons::query()->with(['store' => function ($query) {
            $query->select(["id", "name", "store_address"]);
        }])->where('is_rec', 1)->where('coupon_type', 1)->select(['coupon_name', 'store_id', 'end_time', 'created_at'])->paginate(10);
        return $this->setData($coupons);
    }
    
    private function latest()
    {
        $coupons = Coupons::query()->with(['store' => function ($query) {
            $query->select(["id", "name", "store_address"]);
        }])->where('coupon_type', 1)->select(['coupon_name', 'store_id', 'end_time', 'created_at'])->paginate(10);
        return $this->setData($coupons);
    }
    
    private function search()
    {
        $name     = request('name');
        $storeIds = Store::query()->where('name', 'like', "%$name%")->pluck('id');
        $coupons  = Coupons::query()->with(['store' => function ($query) {
            $query->select(["id", "name", "store_address"]);
        }])->whereIn('store_id', $storeIds)->select(['coupon_name', 'store_id', 'end_time', 'created_at'])->paginate(10);
        return $this->setData($coupons);
    }
    
    
    private function filter()
    {
        $type     = request('type', 1);
        $storeIds = Store::query()->where('type', $type)->pluck('id');
        $coupons  = Coupons::query()->with(['store' => function ($query) {
            $query->select(["id", "name", "store_address"]);
        }])->whereIn('store_id', $storeIds)->select(['coupon_name', 'store_id', 'end_time', 'created_at'])->paginate(10);
        return $this->setData($coupons);
    }
 
      /**
     * @OA\Get(
     *     path="/api/coupon/type/{type}", summary="用户已用优惠券列表",
     *     @OA\Response(response="200", description="{code:0,message:'ok'}"),
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function type(int $type)
    {
        $userId = 1;
        $coupons  = UserCoupons::query()->with(['store' => function ($query) {
            $query->select(["id", "name", "store_address"]);
        }])->where('user_id', $userId)->where('coupon_type', $type)->select(['name', 'store_id', 'use_at', 'created_at'])->paginate(10);
        return $this->setData($coupons);
    }
    /**
     * @OA\Get(
     *     path="/api/coupon/store/{store_id}", summary="商家详情",
     *     @OA\Response(response="200", description="{code:0,message:'ok'，{
    data: {
    id: 商家id,
    name: 商家名称,
    logo: ,
    photo: 店铺图片,
    intro: 介绍,
    store_mobile: 联系电话,
    invite_code: 邀请码,
    invite_id: 上级id,

    store_address: 商家地址
    }
    }}"),
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(int $store_id)
    {
        $store = Store::query()->where('id', $store_id)->first();
        return $this->setData($store);
    }
    
    /**
     * @OA\Get(
     *     path="/api/coupon/circle", summary="遇圈",
     *     @OA\Response(response="200", description="{code:0,message:'ok'}"),
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function circle()
    {
        $coupons = Coupons::query()->with(['store' => function ($query) {
            $query->select(["id", "name", "store_address"]);
        }])->where('coupon_type', 2)->select(['coupon_name', 'store_id', 'end_time', 'created_at'])->paginate(10);
        return $this->setData($coupons);
    }
    
    /**
     * @OA\Get(
     *     path="/api/coupon/used/{coupon_id}", summary="已使用优惠券详情",
     *     @OA\Response(response="200", description="{code:0,message:'ok'}"),
     * )
     * @param int $coupon_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function used(int $coupon_id)
    {
        $coupon = UserCoupons::query()->where('coupon_id', $coupon_id)->first();
        return $this->setData($coupon);
    }
}
