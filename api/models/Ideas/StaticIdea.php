<?php
    requireModel('StaticModel');
    requireModel('Ideas/Traits');

    //**************************************************************
    //
    // Static Idea
    //
    //**************************************************************
    class StaticIdea extends StaticModel {
        use IdeaTraits;
    }