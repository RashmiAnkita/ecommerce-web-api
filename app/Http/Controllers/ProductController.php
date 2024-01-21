<?php

namespace App\Http\Controllers;
use App\Models\ProductCategory;
use App\Models\ProductModel;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class ProductController extends Controller
{

    /*
    |====================================|
    | Add a new product to the database. |
    |====================================|
    */
    public function AddProducts(Request $request){
        //dd($request->all());

        // Validation rules for the incoming request data
        $rules = [
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'product_price' => 'required|numeric|min:0',
            'product_discount_price' => 'nullable|numeric|min:0',
            'product_quantity' => 'required|integer|min:0',
            'product_manufacturer' => 'required|string|max:255',
            'product_images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10000',
        ];

        // Validate the incoming request data
        $validator = Validator::make($request->all(), $rules);

        // If validation fails, then return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
          
        }

        // Get the validated data
        $validatedData = $validator->validated();

        // Extract data from the validated input
        $product_name = $validatedData['product_name'];
        $product_description = $validatedData['product_description'];
        $product_category_id = $request->product_category_id;
        $product_price = $validatedData['product_price'];
        $product_discount_price = $validatedData['product_discount_price'];
        $product_quantity = $validatedData['product_quantity'];
        $product_manufacturer = $validatedData['product_manufacturer'];
        
        // Generate a slug for the product name
        $product_name_string = preg_replace('/[^A-Za-z0-9\-]/', '-', $product_name); 
        $product_name_final_slug = preg_replace('/-+/', '-', $product_name_string); 
        $product_name_slug = strtolower(rtrim($product_name_final_slug, '-'));

        // Check if the generated slug already exists in the database
        $check_slug=ProductModel::where('product_slug',$product_name_slug)->get();
        if(sizeof($check_slug)>0){
            $new_slug=$product_name_slug . uniqid();
        }else{
            $new_slug=$product_name_slug;
        }

        // Process and Store product images
        $product_images = [];
        if ($request->hasFile('product_images')) {
            $product_images_files = $request->file('product_images');
            foreach ($product_images_files as $index => $file) {
                $filename = $file->getClientOriginalName();
                $newFilename = uniqid() . '_' . $filename;
                $file->move(public_path('uploads/products'), $newFilename);
                $product_images[] = $newFilename;
            }
        }

        // Validated data for inserting into the database
        $product_data=[
            'product_name'=>$product_name,
            'product_description'=>$product_description,
            'product_category_id'=>$product_category_id,
            'product_price'=>$product_price,
            'product_discount_price'=>$product_discount_price,
            'product_quantity'=>$product_quantity,
            'product_images'=>json_encode($product_images),         #json_encode Array to string ($product_images[] = $newFilename;)
            'product_manufacturer'=>$product_manufacturer,
            'product_slug'=>$new_slug,
            'created_at'=>now(),
        ];
        //dd($product_data);

        // Insert the product data into the database
        $insert_product=ProductModel::insert($product_data);

        // Respond with success or error message
        if($insert_product){
            return response()->json(['success' =>true,'message'=>'Product added successfully']);
        }else{
            return response()->json(['error' =>true,'message'=>'Something went wrong' ]);
        }

    }


    /*
    |======================================|
    | View all products from the database. |
    |======================================|
    */

    public function ViewAllProducts(){

        $cache = 'all_products';

        // Check if data is in cache
        if (Cache::has($cache)) {
            $allProducts = Cache::get($cache);
        } else {
            // If not in cache, fetch from the database
            // Retrieve all products with associated category details
            $allProducts = ProductModel::where('product_models.product_status', 'A')
                ->leftJoin('product_categories', 'product_categories.id', 'product_models.product_category_id')
                ->select('product_models.*', 'product_categories.category_name')
                ->get();
    
            // Transform product image paths
            $allProducts->transform(function ($product) {
                $product->product_images = json_decode($product->product_images, true);
                $product->product_images = array_map(function ($image) {
                    return asset('uploads/products/' . $image);
                }, $product->product_images);
    
                return $product;
            });
    
            // Cache the result for future requests
            Cache::put($cache, $allProducts, now()->addMinutes(60)); // cache duration
        }
    
        return response()->json(['all-products-details' => $allProducts]);
    }

    /*
    |========================================================|
    | Retrive products data by product id from the database. |
    |========================================================|
    */

    public function EditProductById($product_id){

        // Check the product exists based on product id and return the product details
        $check_product = ProductModel::where('id',$product_id)->where('product_status','A')->first();
        if(!empty($check_product)){
            $check_product->product_images = json_decode($check_product->product_images, true);
            $check_product->product_images = array_map(function ($image) {
                return asset('uploads/products/' . $image);
            }, $check_product->product_images);
            return response()->json(['success'=>true,'edit-products-details' => $check_product]);
        }
        else{
            return response()->json(['error'=>true, 'message'=>'Product not found']);
        }
    }

    /*
    |========================================================|
    | Update product's data by product id from the database. |
    |========================================================|
    */

    public function UpdateProductById(Request $request, $productid){

        // Check the product exists based on product id 
        $check_product = ProductModel::where('id',$productid)->where('product_status','A')->first();

        // If the product exists, proceed with the update
        if(!empty($check_product)){
            
            // Check each field in the request for updates, if not provided, use the existing values
            if(!empty($request->product_name)){
                $product_name = $request->product_name;
            }else{
                $product_name = $check_product->product_name;
            }
            if(!empty($request->product_description)){
                $product_description = $request->product_description;
            }else{
                $product_description = $check_product->product_description;
            }
            if(!empty($request->product_category_id)){
                $product_category_id = $request->product_category_id;
            }else{
                $product_category_id = $check_product->product_category_id;
            }
            if(!empty($request->product_price)){
                $product_price = $request->product_price;
            }else{
                $product_price = $check_product->product_price;
            }
            if(!empty($request->product_discount_price)){
                $product_discount_price = $request->product_discount_price;
            }else{
                $product_discount_price = $check_product->product_discount_price;
            }
            if(!empty($request->product_quantity)){
                $product_quantity = $request->product_quantity;
            }else{
                $product_quantity = $check_product->product_quantity;
            }
            if(!empty($request->product_manufacturer)){
                $product_manufacturer = $request->product_manufacturer;
            }else{
                $product_manufacturer = $check_product->product_manufacturer;
            }
            // Process product images, upload new ones, or use existing ones
            $product_images = [];
            if (!empty($request->file('product_images'))) {
                $product_images_files = $request->file('product_images');
                foreach ($product_images_files as $index => $file) {
                    $filename = $file->getClientOriginalName();
                    $newFilename = uniqid() . '_' . $filename;
                    $file->move(public_path('uploads/products'), $newFilename);
                    $product_images[] = $newFilename;
                }
            } else {
                $product_images = json_decode($check_product->product_images, true); 
            }

            // Generate a slug for the updated product name
            $product_name_string = preg_replace('/[^A-Za-z0-9\-]/', '-', $product_name); 
            $product_name_final_slug = preg_replace('/-+/', '-', $product_name_string); 
            $product_name_slug = strtolower(rtrim($product_name_final_slug, '-'));

            // Check if the new slug already exists in the database
            $check_slug=ProductModel::where('product_slug',$product_name_slug)->get();
            if(sizeof($check_slug)>0){
                $new_slug=$product_name_slug . uniqid();
            }else{
                $new_slug=$product_name_slug;
            }
            
            // Product data for updating the product
            $product_data=[
                'product_name'=>$product_name,
                'product_description'=>$product_description,
                'product_category_id'=>$product_category_id,
                'product_price'=>$product_price,
                'product_discount_price'=>$product_discount_price,
                'product_quantity'=>$product_quantity,
                'product_images'=>json_encode($product_images),        
                'product_manufacturer'=>$product_manufacturer,
                'product_slug'=>$new_slug,
                'updated_at'=>now(),
            ];

            // Update the product in the database
            $update_product=ProductModel::where('id',$productid)->update($product_data);

            // Respond with success or error message
            if($update_product){
                return response()->json(['success' =>true,'message'=>'Product updated successfully']);
            }else{
                return response()->json(['error' =>true,'message'=>'Something went wrong' ]);
            }
        }else{
            // Product with the given ID not found
            return response()->json(['error' =>true,'message'=>'Product not found' ]);
        }
    }

    /*
    |========================================================|
    | Delete product data by product id from the database.   |
    |========================================================|
    */

    public function DeleteProductById($productid){
        // Check the product exists based on product id 
        $check_product = ProductModel::where('id',$productid)->first();
        if(!empty($check_product)){
            // Delete the product id
            $delete_product=ProductModel::where('id',$productid)->delete();
            // Respond with success or error message
            if($delete_product){
                return response()->json(['success' =>true,'message'=>'Product deleted successfully']);
            }else{
                return response()->json(['error' =>true,'message'=>'Something went wrong' ]);
            }
        }else{
            // Product with the given ID not found
            return response()->json(['error' =>true,'message'=>'Product not found' ]);
        }
    }


    /*
    |===============================================================|
    | Update product status data by product id from the database.   |
    |===============================================================|
    */

    public function UpdateProductStatusById(Request $request,$productid){
        // Check the product exists based on product id 
        $check_product = ProductModel::where('id',$productid)->first();
        if(!empty($check_product)){
            //Update the product status in the incoming request and update the current time
            $product_data=[
                'product_status'=>$request->product_status,
                'updated_at'=>now(),
            ];
            // Update the updated product data into database
            $update_product_status=ProductModel::where('id',$productid)->update($product_data);
            // Respond with success or error message
            if($update_product_status){
                return response()->json(['success' =>true,'message'=>'Product status changed successfully']);
            }else{
                return response()->json(['error' =>true,'message'=>'Something went wrong' ]);
            }
        }else{
            // Product with the given ID not found
            return response()->json(['error' =>true,'message'=>'Product not found' ]);
        }
    }
}
