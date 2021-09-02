<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Categories extends Model
{
	protected $connection = 'mongodb';
	protected $collection = 'category_collection';
	protected $primaryKey = '_id';

	protected $fillable = ['category_info'];
}
