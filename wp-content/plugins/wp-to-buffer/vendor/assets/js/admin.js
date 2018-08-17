jQuery( document ).ready( function( $ ) {

	/**
	 * Character Count
	 *
	 * @since 	3.0.0
	 */
	var wp_to_social_pro_character_counting = false;
	var wp_to_social_pro_character_count = function() {

		// If we're currently running an AJAX request, don't run another one
		if ( wp_to_social_pro_character_counting ) {
			return;
		}

        // Find the displayed panel
		$( 'div.sub-panel' ).each( function() {
			if ( $( this ).css( 'display' ) == 'block' ) {
				var active_panel = this,
					statuses = [];

				// Iterate through all textareas within the active panel, getting the status text for each
				$( 'div.status textarea', $( active_panel ) ).each( function() {
					statuses.push( $( this ).val() );
				} );

				// Set a flag so we know we're performing an AJAX request
				wp_to_social_pro_character_counting = true;

				// Send an AJAX request to fetch the parsed statuses and character counts for each status
				$.post( 
					wp_to_social_pro.ajax, 
					{
						'action': 						wp_to_social_pro.character_count_action,
						'post_id': 						wp_to_social_pro.post_id,
						'statuses': 					statuses,
						'nonce': 						wp_to_social_pro.character_count_nonce
					},
					function( response ) {

						// Iterate through the textareas again
						$( 'div.status textarea', $( active_panel ) ).each( function( i ) {
							// Update the character count for this textarea
							$( 'span.character-count', $( this ).parent() ).text( response.data.parsed_statuses[ i ].length );	
						} );

						// Reset the flag
						wp_to_social_pro_character_counting = false;
						
		            }
		        );
			}
		} );
	}

	/**
	 * Character Count Events
	 *
	 * @since 	3.0.0
	 */
	$( '.nav-tab-wrapper a', $( wp_to_social_pro.character_count_metabox ) ).on( 'click', function( e ) {
		wp_to_social_pro_character_count();
	} );
	$( 'input[type="checkbox"]', $( wp_to_social_pro.character_count_metabox ) ).on( 'change', function( e ) {
		wp_to_social_pro_character_count();
	} );
	$( 'div.status textarea', $( wp_to_social_pro.character_count_metabox ) ).on( 'change', function( e ) {
		wp_to_social_pro_character_count();
	} );
	$( 'a.button.add-status', $( wp_to_social_pro.character_count_metabox ) ).on( 'change', function( e ) {
		wp_to_social_pro_character_count();
	} );

	/**
	 * Clear Log
	 *
	 * @since 	3.0.0
	 */
	$( 'a.clear-log' ).on( 'click', function( e ) {

		// Prevent default action
		e.preventDefault();

		// Define button
		var button = $( this );

		// Bail if the button doesn't have an action and a target
		if ( typeof $( button ).data( 'action' ) === undefined || $( button ).data( 'target' ) === undefined ) {
			return;
		}

		// Bail if the user doesn't want to clear the log
		var result = confirm( wp_to_social_pro.clear_log_message );
		if ( ! result ) {
			return;
		}

		// Send AJAX request to clear log
		$.post( 
			wp_to_social_pro.ajax, 
			{
				'action': 		$( button ).data( 'action' ),
				'post': 		$( 'input[name=post_ID]' ).val(),
				'nonce': 		wp_to_social_pro.clear_log_nonce
			},
			function( response ) {

				if ( response.success ) {
					$( 'table.widefat tbody', $( $( button ).data( 'target' ) ) ).html( '<tr><td colspan="3">' + wp_to_social_pro.clear_log_completed + '</td></tr>' );	
				} else {
					alert( response.data );
				}

            }
        );
	} );

	/**
	 * Tags
	 */
	var reinit_tags = function() {

		$( 'select.tags' ).each( function() {
			$( this ).unbind( 'change.wp-to-social-pro' ).on( 'change.wp-to-social-pro', function( e ) {
				// Insert tag into required textarea
				var tag 	= $( this ).val(),
					status 	= $( this ).closest( 'div.status' ),
					sel 	= $( 'textarea', $( status ) ),
					val 	= $( sel ).val();

				$( sel ).val( val += ' ' + tag ).trigger( 'change' );
			} );
		} );

	}
	reinit_tags();

	/**
	 * Initialize selectize instances
	 */
	var reinit_selectize = function( selector ) {

		// Initialize selectize elements
		$( 'select.wpzinc-selectize', $( selector + '-panel' ) ).selectize( {
			valueField: 'id',
    		labelField: 'text',
    		searchField: 'text',
			plugins: ['drag_drop', 'remove_button'],
		    delimiter: ',',
		    persist: false,
		    create: false,
		    load: function( query, callback ) {

		    	// Bail if the number of characters typed isn't enough
		        if ( ! query.length || query.length < 3 ) {
		        	return callback();
		        }

		        // Get action and taxonomy
		        var action = this.$input.data( 'action' ),
		    		taxonomy = this.$input.data( 'taxonomy' );

		        // Perform AJAX request
		        $.ajax( {
		            url: 		wp_to_social_pro.ajax,
		            data: {
		            	action: 	action,
	      				taxonomy: 	taxonomy,
	        			q: 			query,
	        			page: 		10
		            },
		            error: function( jqXHR, textStatus, errorThrown ) {
		                callback();
		            },
		            success: function( result ) {
		                callback( result.data );
		            }
		        } );

		    },

		    /**
		     * Copy Conditional Select Dropdown values to hidden field
		     * as a comma separated string
		     */
		    onChange: function( value ) {
		    	if ( value === null || ! value.length ) {
		    		$( 'input.term-ids', this.$input.closest( 'span.terms' ) ).val( '' );
		    		return;
		    	}
		    	
		    	// Implode into comma separated string
				$( 'input.term-ids', this.$input.closest( 'span.terms' ) ).val( value.join() );
		    }
		} );

	}

	/**
	 * Destroy all selectize instances
	 */
	var destroy_selectize = function() {

		$( 'select.wpzinc-selectize' ).selectize().each( function() {
			this.selectize.destroy();
		} );

	}

	/**
	 * Destroy all selectize instances, and only re-initialize selectize
	 * instances that are on display in the DOM
	 */
	$( '.nav-tab-wrapper' ).on( 'change', function( e, tab ) {

		// Ignore tab changes if they are not profile related
		if ( $( this ).prop( 'tagName' ) != 'H3' ) {
			return;
		}

		// Destroy selectize instances
		destroy_selectize();

		// Initialize visible selectize instances
		reinit_selectize( $( tab ).attr( 'href' ) );
		
	} );
	
	/**
	 * Add Status Update
	 */
	$( 'body' ).on( 'click', 'a.button.add-status', function( e ) {

		e.preventDefault();

		// Destroy selectize instances
		destroy_selectize();

		// Setup vars
		var button 				= $( this ),
			button_container 	= $( button ).parent(),
			statuses_container 	= $( button ).closest( 'div.statuses' ),
			status 				= $( button_container ).prev().html(),
			sub_panel 			= $( statuses_container ).closest( 'div.sub-panel' );

		// Clone status
		$( button_container ).before( '<div class="option sortable">' + status + '</div>' );

		// Reindex statuses
		reindex_statuses( $( statuses_container ) );

		// Reload sortable
		$( 'div.statuses' ).sortable( 'refresh' );
		
		// Reload conditionals
		$( 'input,select' ).conditional();

		// Reload tag selector
		reinit_tags();

		// Reload selectize
		reinit_selectize( '#' + $( sub_panel ).attr( 'id' ).replace( '-panel', '' ) );

    } );

	/**
	 * Reorder Status Updates
	 */
	$( 'div.statuses' ).sortable( {
		containment: 'parent',
		items: '.sortable',
		stop: function( e, ui ) {
			// Get status and container
			var status 				= $( ui.item ),
				statuses_container 	= $( status ).closest( 'div.statuses' );

			// Reindex statuses
			reindex_statuses( $( statuses_container ) );
		}
	} );

	/**
	 * Schedule Options
	 */
	var wp_to_social_pro_schedules = function() {

		// Bail if no schedule dropdowns
		if ( $( 'select.schedule' ).length == 0 ) {
			return;
		}

		// Iterate through each, showing / hiding relative fields
		$( 'select.schedule' ).each( function( i ) {
			switch ( $( this ).val() ) {
				case 'custom':
					$( 'span.schedule', $( this ).parent() ).show();
					$( 'span.custom', $( this ).parent() ).show();
					$( 'span.custom_field', $( this ).parent() ).hide();
					break;

				case 'custom_field':
					$( 'span.schedule', $( this ).parent() ).show();
					$( 'span.custom', $( this ).parent() ).hide();
					$( 'span.custom_field', $( this ).parent() ).show();
					break;

				default:
					// Hide additonal schedule options
					$( 'span.schedule', $( this ).parent() ).hide();
					$( 'span.custom', $( this ).parent() ).hide();
					$( 'span.custom_field', $( this ).parent() ).hide();
					break;
			}
		} );
	
	}
	$( 'body' ).on( 'change', 'select.schedule', function( e ) {

		wp_to_social_pro_schedules();

	} );
	wp_to_social_pro_schedules();	

	/**
	 * Force focus on inputs, so they can be accessed on mobile.
	 * For some reason using jQuery UI sortable prevents us accessing textareas on mobile
	 * See http://bugs.jqueryui.com/ticket/4429
	 */
	$( 'div.statuses' ).bind( 'click.sortable mousedown.sortable', function( e ) {
		e.target.focus();
	} );

	/**
	 * Delete Status Update
	 */
	$( 'div.sub-panel' ).on( 'click', 'a.delete', function( e ) {

		e.preventDefault();

		// Confirm deletion
		var result = confirm( wp_to_social_pro.delete_status_message );
		if ( ! result ) {
			return;
		}

		// Get status and container
		var status 				= $( this ).closest( 'div.option' ),
			statuses_container 	= $( status ).closest( 'div.statuses' ),
			sub_panel 			= $( statuses_container ).closest( 'div.sub-panel' );

		// Delete status
		$( status ).remove();

		// Reindex statuses
		reindex_statuses( $( statuses_container ) );

		// Destroy selectize instances
		destroy_selectize();

		// Reload selectize
		reinit_selectize( '#' + $( sub_panel ).attr( 'id' ).replace( '-panel', '' ) );

	} );

	/**
	 * Changes the displayed index on each status within the given container
	 *
	 * @since 	3.0.0
	 *
	 * @param 	obj 	status_container  		Status Container
	*/
	var reindex_statuses = function( statuses_container ) {

		// Find all sortable options in the status container (these are individual statuses)
		// and reindex them from 1
		$( 'div.option.sortable', $( statuses_container ) ).each( function( i ) {
			$( 'div.number a.count ', $( this ) ).html( '#' + ( i + 1 ) );

			// Set 'first' class
			if ( i == 0 ) {
				$( this ).addClass( 'first' );
			} else {
				$( this ).removeClass( 'first' );
			}
		} );

	}

	/**
	 * Datepicker
	 */
	$( 'input.datepicker' ).datepicker( {
		dateFormat: 'yy-mm-dd',
		maxDate: 0,
	} );

	/**
	 * Bulk Publishing: Add Table Row (Condition)
	 */
	$( 'a.button.add-table-row' ).on( 'click', function( e ) {

		e.preventDefault();

		// Setup vars
		var button 				= $( this ),
			table 				= $( button ).closest( 'table' ),
			row 				= $( 'tbody tr:first-child', $( table ) );

		// Clone row
		$( 'tbody tr:last-child', $( table ) ).after( '<tr>' + $( row ).html() + '</tr>' );

    } );

    /**
	 * Bulk Publishing: Delete Table Row (Condition)
	 */
	$( document ).on( 'click', 'a.button.delete-table-row', function( e ) {

		e.preventDefault();

		// Setup vars
		var button 				= $( this ),
			row 				= $( this ).closest( 'tr' );

		// Remove row
		$( row ).remove();

    } );

	/**
	 * Select All
	 */
	$( 'body.wpzinc' ).on( 'change', 'input[name=toggle]', function( e ) {
		// Change
		if ( $( this ).is( ':checked' ) ) {
			$( 'ul.categorychecklist input[type=checkbox]' ).prop( 'checked', true );
		} else {
			$( 'ul.categorychecklist input[type=checkbox]' ).prop( 'checked', false );
		}
	} );

} );