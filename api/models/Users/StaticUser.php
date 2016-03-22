<?php
    
    requireModel('StaticModel');
    requireModel('Users/Traits');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticUser extends StaticModel {
        use UserTraits;
    }