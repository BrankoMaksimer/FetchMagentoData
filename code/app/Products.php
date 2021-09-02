<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Products extends Model
{
	protected $connection = 'mongodb';
	protected $collection = 'products_collection';
	protected $primaryKey = 'id';

	protected $fillable = ['id','product_info'];
}
