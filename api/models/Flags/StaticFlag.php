<?php
    
    requireModel('StaticModel');
    requireModel('Flags/Traits');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticFlag extends StaticModel {
        use FlagTraits; 
    }   