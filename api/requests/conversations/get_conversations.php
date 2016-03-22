<?php
    requireCollection('ConversationsCollection');
	
	if ( isset($__request__[3]) && !empty($__request__[3]) ) {
		$id = $__request__[3];
	} else {
		$id = null;
	}

	if ( $id ) {
		$conversation = new MessagesCollection();
		$conversation->setConversation( $id );

		$json['conversation'] = $conversation->get();	
	}