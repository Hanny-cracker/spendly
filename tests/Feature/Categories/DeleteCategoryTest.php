<?php

use App\Actions\Categories\DeleteCategory;
use App\Models\Category;


it('can delete a category without transactions', function(){

    $user=$this->createUser();


    $category=$this->createCategory(
        user:$user
    );


    app(DeleteCategory::class)
        ->handle($category);


    expect(
        Category::find($category->id)
    )
    ->toBeNull();


});

it('cannot delete category with transactions', function(){

    $user=$this->createUser();


    $category=$this->createCategory(
        user:$user
    );


    $account=$this->createAccount(
        user:$user
    );


    $this->createTransaction(
        user:$user,
        account:$account,
        category:$category
    );


    expect(fn()=> app(DeleteCategory::class)
        ->handle($category)
    )
    ->toThrow(Exception::class);


});