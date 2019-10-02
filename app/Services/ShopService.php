<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;

class ShopService extends Service
{
    /**********************************************************************************************
     
        SHOPS

    **********************************************************************************************/
    public function createShop($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateShopData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $shop = Shop::create($data);

            if ($image) $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    public function updateShop($shop, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Shop::where('name', $data['name'])->where('id', '!=', $shop->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateShopData($data, $shop);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $shop->update($data);

            if ($shop) $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    public function updateShopStock($shop, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Clear the existing shop stock
            $shop->stock()->delete();

            //dd($data);

            foreach($data['item_id'] as $key => $itemId)
            {
                $shop->stock()->create([
                    'shop_id'               => $shop->id,
                    'item_id'               => $data['item_id'][$key],
                    'currency_id'           => $data['currency_id'][$key],
                    'cost'                  => $data['cost'][$key],
                    'use_user_bank'         => isset($data['use_user_bank'][$key]),
                    'use_character_bank'    => isset($data['use_character_bank'][$key]),
                    'is_limited_stock'      => isset($data['is_limited_stock'][$key]),
                    'quantity'              => isset($data['is_limited_stock'][$key]) ? $data['quantity'][$key] : 0,
                    'purchase_limit'        => $data['purchase_limit'][$key],
                ]);
            }

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    private function populateShopData($data, $shop = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);
        
        if(isset($data['remove_image']))
        {
            if($shop && $shop->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }
    
    public function deleteShop($shop)
    {
        DB::beginTransaction();

        try {
            // Check first if the shop is currently in use
            //if(ShopStock::where('shop_id', $shop->id)->exists()) throw new \Exception("The shop contains some items. Please remove them before deleting the shop.");
            
            // Delete shop stock

            if($shop->has_image) $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName); 
            $shop->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    public function sortShop($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Shop::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    
    /**********************************************************************************************
     
        ITEMS

    **********************************************************************************************/

    public function createItem($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['item_category_id']) && $data['item_category_id'] == 'none') $data['item_category_id'] = null;

            if((isset($data['item_category_id']) && $data['item_category_id']) && !ItemCategory::where('id', $data['item_category_id'])->exists()) throw new \Exception("The selected item category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $item = Item::create($data);

            if ($image) $this->handleImage($image, $item->imagePath, $item->imageFileName);

            return $this->commitReturn($item);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    public function updateItem($item, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['item_category_id']) && $data['item_category_id'] == 'none') $data['item_category_id'] = null;

            // More specific validation
            if(Item::where('name', $data['name'])->where('id', '!=', $item->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['item_category_id']) && $data['item_category_id']) && !ItemCategory::where('id', $data['item_category_id'])->exists()) throw new \Exception("The selected item category is invalid.");

            $data = $this->populateData($data);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $item->update($data);

            if ($item) $this->handleImage($image, $item->ImagePath, $item->ImageFileName);

            return $this->commitReturn($item);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    private function populateData($data, $item = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        
        if(!isset($data['allow_transfer'])) $data['allow_transfer'] = 0;

        if(isset($data['remove_image']))
        {
            if($item && $item->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($item->ImagePath, $item->ImageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }
    
    public function deleteItem($item)
    {
        DB::beginTransaction();

        try {
            // Check first if the item is currently owned
            if(DB::table('inventory')->where('item_id', $item->id)->exists()) throw new \Exception("At least one user currently owns this item. Please remove the item(s) before deleting it.");
            
            if($item->has_image) $this->deleteImage($item->ImagePath, $item->ImageFileName); 
            $item->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}