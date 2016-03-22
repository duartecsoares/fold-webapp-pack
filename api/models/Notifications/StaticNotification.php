<?php

    requireModel('Notifications/Traits');
    requireModel('StaticModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticNotification extends StaticModel {
        
        use NotificationTraits; 
    }   