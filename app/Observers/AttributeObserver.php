<?php
namespace App\Observers;

use App\Jobs\SyncOneProductToES;
use App\Models\Attribute;

class AttributeObserver
{
    // creating, created, updating, updated, saving, saved, deleting, deleted, restoring, restored

    public function saved(Attribute $attribute)
    {
        if($attribute->product){
            dispatch(new SyncOneProductToES($attribute->product));
        }
    }

    public function deleted(Attribute $attribute)
    {
        if($attribute->product){
            dispatch(new SyncOneProductToES($attribute->product));
        }
    }
}