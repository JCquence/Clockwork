<?php
    /*
        Possible creation of objects:
        
        new User();                         // Empty user
        new User(1);                        // Fetch user with id 1 from the database
        new User('value', 'key');           // Fetch the first user where key = value from database
        new User(['firstName' => 'Henk']);  // Create user with set values
    */
    
    Database::debug();
    
    $query = new Query();
    $query->where("name = 'Kevin'");
    
    // --- create user
    $user = new user('Kevin', 'name');
    var_dump($user);
    
    // --- set values
    //$user->set('name', 'Kevin');
    //$user->set('lastName', 'Kaandorp');

    
    // --- save to database
    //$user->save();
    
    //dump
    //dump($user->getValues());
    
    // --- assign variables to view
    $this->assign('user', $user);
?>