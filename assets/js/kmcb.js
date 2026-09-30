/**
 * Products Grid — AJAX filtering.
 */
( function () {
	'use strict';

	var SELECTOR = '[data-kmcb]';

	function debounce( fn, wait ) {
		var timer;
		return function () {
			var args = arguments;
			var self = this;
			clearTimeout( timer );
			timer = setTimeout( function () {
				fn.apply( self, args );
			}, wait );
		};
	}

	function Grid( root ) {
		this.root = root;
		this.form = root.querySelector( '[data-kmcb-form]' );
		this.content = root.querySelector( '[data-kmcb-content]' );
		this.sidebar = root.querySelector( '[data-kmcb-sidebar]' );
		this.postId = root.getAttribute( 'data-post' );
		this.widgetId = root.getAttribute( 'data-widget' );
		this.sync = '1' === root.getAttribute( 'data-sync' );
		this.paged = 1;
		this.controller = null;

		this.bind();
		this.fitSort();
		root.setAttribute( 'data-kmcb-ready', '1' );
	}

	/**
	 * Shrink the sort control to the option actually showing.
	 *
	 * A select is as wide as its widest option, so the box stays the width of
	 * "Price: High to Low" whatever is selected. The native arrow sits at the
	 * end of that box, which right to left is the far left, leaving a gap
	 * between the arrow and the short word beside it.
	 */
	Grid.prototype.fitSort = function () {
		var select = this.root.querySelector( '[data-kmcb-sort]' );

		if ( ! select || ! select.options.length ) {
			return;
		}

		// Where the browser can size a select to its own content it already
		// does this, and better. Measuring on top of that is what made the
		// control too narrow: its natural width is then the selected option
		// rather than the widest, so the room left for the arrow came out as
		// nothing and the text was clipped.
		if ( window.CSS && window.CSS.supports && window.CSS.supports( 'field-sizing', 'content' ) ) {
			return;
		}

		var ruler = document.createElement( 'span' );

		ruler.setAttribute( 'aria-hidden', 'true' );
		ruler.style.cssText = 'position:absolute;top:-9999px;left:-9999px;visibility:hidden;white-space:pre;';

		var measure = function ( text ) {
			var style = window.getComputedStyle( select );

			ruler.style.fontFamily = style.fontFamily;
			ruler.style.fontSize = style.fontSize;
			ruler.style.fontWeight = style.fontWeight;
			ruler.style.fontStyle = style.fontStyle;
			ruler.style.letterSpacing = style.letterSpacing;
			ruler.textContent = text;

			document.body.appendChild( ruler );
			var width = ruler.getBoundingClientRect().width;
			document.body.removeChild( ruler );

			return width;
		};

		var fit = function () {
			var option = select.options[ select.selectedIndex ];

			if ( ! option ) {
				return;
			}

			// Left alone the select is its widest option plus the arrow,
			// padding and border, so the difference between the two is what
			// that chrome takes. The floor is there because a wrong answer
			// that is too small clips the text, which is worse than a gap.
			select.style.width = '';

			var widest = 0;

			Array.prototype.forEach.call( select.options, function ( each ) {
				widest = Math.max( widest, measure( each.text ) );
			} );

			var chrome = Math.max( 20, select.getBoundingClientRect().width - widest );

			select.style.width = Math.ceil( measure( option.text ) + chrome ) + 'px';
		};

		fit();
		select.addEventListener( 'change', fit );

		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( fit );
		}
	};

	Grid.prototype.bind = function () {
		var self = this;

		if ( this.form ) {
			this.form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				self.request( 1, false );
			} );
		}

		this.root.addEventListener( 'change', function ( event ) {
			var target = event.target;

			if ( target.matches( '[data-kmcb-option]' ) ) {
				self.syncChecked( target );
				self.request( 1, false );
				return;
			}

			if ( target.matches( '[data-kmcb-sort]' ) ) {
				self.request( 1, false );
			}
		} );

		this.root.addEventListener( 'click', function ( event ) {
			var el;

			el = event.target.closest( '[data-kmcb-group-toggle]' );
			if ( el ) {
				event.preventDefault();
				self.toggleGroup( el );
				return;
			}

			el = event.target.closest( '[data-kmcb-mobile-toggle]' );
			if ( el ) {
				event.preventDefault();
				var open = self.root.classList.toggle( 'is-filters-open' );
				el.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				return;
			}

			el = event.target.closest( '[data-kmcb-tab]' );
			if ( el ) {
				event.preventDefault();
				self.setTab( el.getAttribute( 'data-kmcb-tab' ) );
				return;
			}

			el = event.target.closest( '[data-kmcb-view]' );
			if ( el ) {
				event.preventDefault();
				self.setView( el.getAttribute( 'data-kmcb-view' ) );
				return;
			}

			el = event.target.closest( '[data-kmcb-clear]' );
			if ( el ) {
				event.preventDefault();
				self.clear();
				return;
			}

			el = event.target.closest( '[data-kmcb-load-more]' );
			if ( el ) {
				event.preventDefault();
				self.request( parseInt( el.getAttribute( 'data-paged' ), 10 ) || 2, true );
				return;
			}

			el = event.target.closest( '[data-kmcb-page]' );
			if ( el ) {
				event.preventDefault();
				self.request( parseInt( el.getAttribute( 'data-kmcb-page' ), 10 ) || 1, false );
			}
		} );

		var search = this.root.querySelector( '[data-kmcb-search]' );
		if ( search ) {
			search.addEventListener(
				'input',
				debounce( function () {
					self.request( 1, false );
				}, 350 )
			);
			search.addEventListener( 'keydown', function ( event ) {
				if ( 'Enter' === event.key ) {
					event.preventDefault();
					self.request( 1, false );
				}
			} );
		}
	};

	/**
	 * Keep the `is-checked` helper class in step with the real inputs (radios
	 * clear their siblings, so refresh the whole group).
	 */
	Grid.prototype.syncChecked = function ( input ) {
		var group = input.closest( '[data-kmcb-group]' );
		var scope = group || this.root;
		var options = scope.querySelectorAll( '[data-kmcb-option]' );

		Array.prototype.forEach.call( options, function ( option ) {
			var label = option.closest( '.kmcb-opt' );
			if ( label ) {
				label.classList.toggle( 'is-checked', option.checked );
			}
		} );
	};

	Grid.prototype.toggleGroup = function ( button ) {
		var group = button.closest( '[data-kmcb-group]' );
		if ( ! group ) {
			return;
		}
		var open = group.classList.toggle( 'is-open' );
		button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	};

	Grid.prototype.input = function ( name ) {
		return this.root.querySelector( '[data-kmcb-input="' + name + '"]' );
	};

	Grid.prototype.setTab = function ( slug ) {
		var field = this.input( 'tab' );
		if ( field ) {
			field.value = slug;
		}

		var tabs = this.root.querySelectorAll( '[data-kmcb-tab]' );
		Array.prototype.forEach.call( tabs, function ( tab ) {
			var active = tab.getAttribute( 'data-kmcb-tab' ) === slug;
			tab.classList.toggle( 'is-active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );

		this.request( 1, false );
	};

	Grid.prototype.setView = function ( view ) {
		var field = this.input( 'view' );
		if ( field ) {
			field.value = view;
		}

		this.root.classList.remove( 'kmcb--view-grid', 'kmcb--view-list' );
		this.root.classList.add( 'kmcb--view-' + view );
		this.root.setAttribute( 'data-view', view );

		var grid = this.root.querySelector( '[data-kmcb-grid]' );
		if ( grid ) {
			grid.classList.remove( 'kmcb-grid--grid', 'kmcb-grid--list' );
			grid.classList.add( 'kmcb-grid--' + view );
		}

		var buttons = this.root.querySelectorAll( '[data-kmcb-view]' );
		Array.prototype.forEach.call( buttons, function ( button ) {
			var active = button.getAttribute( 'data-kmcb-view' ) === view;
			button.classList.toggle( 'is-active', active );
			button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		} );

		this.pushUrl();
	};

	Grid.prototype.clear = function () {
		var options = this.root.querySelectorAll( '[data-kmcb-option]' );
		Array.prototype.forEach.call( options, function ( option ) {
			option.checked = false;
			var label = option.closest( '.kmcb-opt' );
			if ( label ) {
				label.classList.remove( 'is-checked' );
			}
		} );

		var search = this.root.querySelector( '[data-kmcb-search]' );
		if ( search ) {
			search.value = '';
		}

		var tab = this.input( 'tab' );
		if ( tab ) {
			tab.value = '';
		}

		var tabs = this.root.querySelectorAll( '[data-kmcb-tab]' );
		Array.prototype.forEach.call( tabs, function ( el ) {
			var active = '' === el.getAttribute( 'data-kmcb-tab' );
			el.classList.toggle( 'is-active', active );
			el.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );

		this.request( 1, false );
	};

	/**
	 * Read the current UI into a payload.
	 */
	Grid.prototype.collect = function ( paged, append ) {
		var data = new FormData();

		data.append( 'action', 'kmcb_filter' );
		data.append( 'nonce', window.KMCB_DATA.nonce );
		data.append( 'post_id', this.postId );
		data.append( 'widget_id', this.widgetId );
		data.append( 'paged', paged );

		if ( append ) {
			data.append( 'append', '1' );
		}

		var groups = this.root.querySelectorAll( '[data-kmcb-group]' );
		Array.prototype.forEach.call( groups, function ( group ) {
			var taxonomy = group.getAttribute( 'data-kmcb-group' );
			var checked = group.querySelectorAll( '[data-kmcb-option]:checked' );

			Array.prototype.forEach.call( checked, function ( option ) {
				data.append( 'f[' + taxonomy + '][]', option.value );
			} );
		} );

		var search = this.root.querySelector( '[data-kmcb-search]' );
		data.append( 's', search ? search.value : '' );

		var sort = this.root.querySelector( '[data-kmcb-sort]' );
		if ( sort ) {
			data.append( 'orderby', sort.value );
		}

		var tab = this.input( 'tab' );
		data.append( 'tab', tab ? tab.value : '' );

		var view = this.input( 'view' );
		data.append( 'view', view ? view.value : 'grid' );

		return data;
	};

	Grid.prototype.request = function ( paged, append ) {
		var self = this;

		if ( this.controller ) {
			this.controller.abort();
		}

		this.controller = window.AbortController ? new AbortController() : null;

		this.root.classList.add( 'is-loading' );
		if ( this.content ) {
			this.content.setAttribute( 'aria-busy', 'true' );
		}

		var options = {
			method: 'POST',
			credentials: 'same-origin',
			body: this.collect( paged, append ),
		};

		if ( this.controller ) {
			options.signal = this.controller.signal;
		}

		fetch( window.KMCB_DATA.ajaxUrl, options )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( payload ) {
				if ( ! payload || ! payload.success ) {
					throw new Error( 'kmcb: request failed' );
				}
				self.apply( payload.data, append );
			} )
			.catch( function ( error ) {
				if ( error && 'AbortError' === error.name ) {
					return;
				}
				self.done();
			} );
	};

	Grid.prototype.apply = function ( data, append ) {
		this.paged = data.paged;

		if ( append ) {
			var grid = this.root.querySelector( '[data-kmcb-grid]' );
			if ( grid ) {
				grid.insertAdjacentHTML( 'beforeend', data.cards );
			}
			this.updatePagination( data );
		} else {
			if ( this.content ) {
				this.content.innerHTML = data.grid;
			}
			if ( this.sidebar && data.sidebar ) {
				this.sidebar.innerHTML = data.sidebar;
			}
			this.scrollIntoView();
		}

		var count = this.root.querySelector( '[data-kmcb-count]' );
		if ( count ) {
			count.textContent = data.count;
		}

		this.pushUrl( data.query );
		this.done();
	};

	/**
	 * Retarget the "load more" button after an append.
	 */
	Grid.prototype.updatePagination = function ( data ) {
		var button = this.root.querySelector( '[data-kmcb-load-more]' );

		if ( ! button ) {
			return;
		}

		if ( data.hasMore ) {
			button.setAttribute( 'data-paged', data.paged + 1 );
			button.disabled = false;
			button.hidden = false;
		} else {
			button.disabled = true;
			button.hidden = true;
		}
	};

	Grid.prototype.scrollIntoView = function () {
		var box = this.root.getBoundingClientRect();

		if ( box.top < 0 ) {
			window.scrollTo( {
				top: box.top + window.pageYOffset - 20,
				behavior: 'smooth',
			} );
		}
	};

	Grid.prototype.pushUrl = function ( query ) {
		if ( ! this.sync || ! window.history || ! window.history.replaceState ) {
			return;
		}

		if ( 'undefined' === typeof query ) {
			return;
		}

		var url = window.location.pathname + ( query ? '?' + query : '' ) + window.location.hash;
		window.history.replaceState( {}, '', url );
	};

	Grid.prototype.done = function () {
		this.root.classList.remove( 'is-loading' );
		if ( this.content ) {
			this.content.setAttribute( 'aria-busy', 'false' );
		}
		this.controller = null;
	};

	/**
	 * Single product gallery: clicking a thumbnail swaps the main image.
	 */
	function Gallery( root ) {
		var main = root.querySelector( '.kmcb-gallery-img' );

		if ( ! main ) {
			return;
		}

		root.addEventListener( 'click', function ( event ) {
			var thumb = event.target.closest( '[data-kmcb-thumb]' );

			if ( ! thumb ) {
				return;
			}

			event.preventDefault();

			var full = thumb.getAttribute( 'data-full' );

			if ( full ) {
				main.src = full;
				var srcset = thumb.getAttribute( 'data-srcset' );
				if ( srcset ) {
					main.srcset = srcset;
				} else {
					main.removeAttribute( 'srcset' );
				}
			}

			var thumbs = root.querySelectorAll( '[data-kmcb-thumb]' );
			Array.prototype.forEach.call( thumbs, function ( other ) {
				var active = other === thumb;
				other.classList.toggle( 'is-active', active );
				other.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			} );
		} );

		root.setAttribute( 'data-kmcb-ready', '1' );
	}

	/**
	 * Product variations: boxes per attribute. Choosing one shows that
	 * variation's image in the Product Gallery, and choosing a gallery
	 * thumbnail that belongs to a variation selects its box.
	 */
	function Variations( root ) {
		var variations = [];

		try {
			variations = JSON.parse( root.getAttribute( 'data-variations' ) || '[]' );
		} catch ( e ) {
			variations = [];
		}

		var swap   = '1' === root.getAttribute( 'data-swap' );
		var useUrl = '1' === root.getAttribute( 'data-url' );
		var groups = root.querySelectorAll( '[data-attribute]' );

		function current() {
			var chosen = {};
			Array.prototype.forEach.call( groups, function ( group ) {
				var active = group.querySelector( '.kmcb-var-box.is-active' );
				chosen[ group.getAttribute( 'data-attribute' ) ] = active ? active.getAttribute( 'data-value' ) : '';
			} );
			return chosen;
		}

		// A variation saved with "Any" for an attribute has an empty value there.
		function matches( variation, chosen ) {
			return Object.keys( chosen ).every( function ( name ) {
				var value = variation.attributes[ name ];
				return ! value || ! chosen[ name ] || value === chosen[ name ];
			} );
		}

		function find( chosen ) {
			for ( var i = 0; i < variations.length; i++ ) {
				if ( matches( variations[ i ], chosen ) ) {
					return variations[ i ];
				}
			}
			return null;
		}

		function galleries() {
			return document.querySelectorAll( '[data-kmcb-gallery]' );
		}

		function showImage( variation ) {
			if ( ! swap || ! variation || ! variation.image ) {
				return;
			}

			Array.prototype.forEach.call( galleries(), function ( gallery ) {
				var main = gallery.querySelector( '.kmcb-gallery-img' );

				if ( ! main ) {
					return;
				}

				main.src = variation.image;
				if ( variation.srcset ) {
					main.srcset = variation.srcset;
				} else {
					main.removeAttribute( 'srcset' );
				}

				Array.prototype.forEach.call( gallery.querySelectorAll( '[data-kmcb-thumb]' ), function ( thumb ) {
					var active = String( variation.image_id ) === thumb.getAttribute( 'data-id' );
					thumb.classList.toggle( 'is-active', active );
					thumb.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				} );
			} );
		}

		function setBox( box ) {
			var group = box.closest( '[data-attribute]' );

			Array.prototype.forEach.call( group.querySelectorAll( '.kmcb-var-box' ), function ( other ) {
				var active = other === box;
				other.classList.toggle( 'is-active', active );
				other.setAttribute( 'aria-checked', active ? 'true' : 'false' );
			} );
		}

		function refresh( fromThumb, initial ) {
			var chosen    = current();
			var variation = find( chosen );

			// Grey out options no variation offers alongside the other choices.
			Array.prototype.forEach.call( groups, function ( group ) {
				var name = group.getAttribute( 'data-attribute' );
				Array.prototype.forEach.call( group.querySelectorAll( '.kmcb-var-box' ), function ( box ) {
					var test = Object.assign( {}, chosen );
					test[ name ] = box.getAttribute( 'data-value' );
					box.classList.toggle( 'is-unavailable', ! find( test ) );
				} );
			} );

			if ( ! fromThumb ) {
				showImage( variation );
			}

			// Only a real choice goes in the address, not the page opening.
			if ( ! initial && useUrl && window.history && window.history.replaceState ) {
				var url = new URL( window.location.href );
				Object.keys( chosen ).forEach( function ( name ) {
					if ( chosen[ name ] ) {
						url.searchParams.set( name, chosen[ name ] );
					}
				} );
				window.history.replaceState( window.history.state, '', url.toString() );
			}

			root.dispatchEvent( new CustomEvent( 'kmcb:variation', { bubbles: true, detail: { variation: variation, attributes: chosen } } ) );
		}

		root.addEventListener( 'click', function ( event ) {
			var box = event.target.closest( '.kmcb-var-box' );

			if ( ! box || ! root.contains( box ) ) {
				return;
			}

			event.preventDefault();
			setBox( box );
			refresh( false );
		} );

		// Arrow keys move between boxes, as in any radio group.
		root.addEventListener( 'keydown', function ( event ) {
			var box = event.target.closest( '.kmcb-var-box' );

			if ( ! box || -1 === [ 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown' ].indexOf( event.key ) ) {
				return;
			}

			var boxes = Array.prototype.slice.call( box.parentNode.querySelectorAll( '.kmcb-var-box' ) );
			var rtl   = 'rtl' === getComputedStyle( box ).direction;
			var step  = ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) ? 1 : -1;

			if ( rtl && ( 'ArrowLeft' === event.key || 'ArrowRight' === event.key ) ) {
				step = -step;
			}

			var next = boxes[ ( boxes.indexOf( box ) + step + boxes.length ) % boxes.length ];

			event.preventDefault();
			next.focus();
			setBox( next );
			refresh( false );
		} );

		// A gallery thumbnail that is a variation's image selects that variation.
		document.addEventListener( 'click', function ( event ) {
			var thumb = event.target.closest( '[data-kmcb-thumb]' );

			// The editor re-renders widgets; a replaced one stops listening.
			if ( ! thumb || ! document.contains( root ) ) {
				return;
			}

			var id = thumb.getAttribute( 'data-id' );

			for ( var i = 0; i < variations.length; i++ ) {
				if ( id && String( variations[ i ].image_id ) === id ) {
					var attributes = variations[ i ].attributes;

					Object.keys( attributes ).forEach( function ( name ) {
						if ( ! attributes[ name ] ) {
							return;
						}
						Array.prototype.forEach.call( groups, function ( group ) {
							if ( group.getAttribute( 'data-attribute' ) !== name ) {
								return;
							}
							Array.prototype.forEach.call( group.querySelectorAll( '.kmcb-var-box' ), function ( box ) {
								if ( box.getAttribute( 'data-value' ) === attributes[ name ] ) {
									setBox( box );
								}
							} );
						} );
					} );

					refresh( true );
					return;
				}
			}
		} );

		refresh( false, true );

		root.setAttribute( 'data-kmcb-ready', '1' );
	}

	/**
	 * Product accordion.
	 */
	function Accordion( root ) {
		var one = '1' === root.getAttribute( 'data-one' );

		root.addEventListener( 'click', function ( event ) {
			var head = event.target.closest( '[data-kmcb-acc-toggle]' );

			if ( ! head ) {
				return;
			}

			event.preventDefault();

			var item = head.closest( '.kmcb-acc-item' );
			var body = document.getElementById( head.getAttribute( 'aria-controls' ) );
			var open = 'true' !== head.getAttribute( 'aria-expanded' );

			if ( one && open ) {
				Array.prototype.forEach.call( root.querySelectorAll( '.kmcb-acc-item' ), function ( other ) {
					if ( other === item ) {
						return;
					}
					other.classList.remove( 'is-open' );
					var otherHead = other.querySelector( '[data-kmcb-acc-toggle]' );
					var otherBody = other.querySelector( '.kmcb-acc-body' );
					if ( otherHead ) {
						otherHead.setAttribute( 'aria-expanded', 'false' );
					}
					if ( otherBody ) {
						otherBody.hidden = true;
					}
				} );
			}

			item.classList.toggle( 'is-open', open );
			head.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			if ( body ) {
				body.hidden = ! open;
			}
		} );

		root.setAttribute( 'data-kmcb-ready', '1' );
	}

	/**
	 * Navbar: collapses below its own breakpoint, which is a per-widget
	 * setting rather than a fixed media query.
	 */
	function Navbar( root ) {
		var toggle = root.querySelector( '[data-kmcb-nav-toggle]' );

		var breakpoint = function () {
			var value = parseInt( getComputedStyle( root ).getPropertyValue( '--kmcb-nav-break' ), 10 );
			return isNaN( value ) ? 1024 : value;
		};

		var apply = function () {
			var collapsed = window.innerWidth < breakpoint();
			root.classList.toggle( 'kmcb-nav--collapsed', collapsed );

			if ( ! collapsed ) {
				root.classList.remove( 'is-open' );
				if ( toggle ) {
					toggle.setAttribute( 'aria-expanded', 'false' );
				}
			}
		};

		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var open = root.classList.toggle( 'is-open' );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		}

		// Close it after following a link on a one-page layout.
		root.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '.kmcb-nav-panel a' ) ) {
				root.classList.remove( 'is-open' );
				if ( toggle ) {
					toggle.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );

		window.addEventListener( 'resize', debounce( apply, 150 ) );
		apply();

		root.setAttribute( 'data-kmcb-ready', '1' );
	}

	function init( scope ) {
		var context = scope && scope.querySelectorAll ? scope : document;

		Array.prototype.forEach.call( context.querySelectorAll( SELECTOR ), function ( root ) {
			if ( root.getAttribute( 'data-kmcb-ready' ) ) {
				return;
			}
			new Grid( root );
		} );

		Array.prototype.forEach.call( context.querySelectorAll( '[data-kmcb-gallery]' ), function ( root ) {
			if ( root.getAttribute( 'data-kmcb-ready' ) ) {
				return;
			}
			Gallery( root );
		} );

		// After the galleries, so the first choice can set their image.
		Array.prototype.forEach.call( context.querySelectorAll( '[data-kmcb-variations]' ), function ( root ) {
			if ( root.getAttribute( 'data-kmcb-ready' ) ) {
				return;
			}
			Variations( root );
		} );

		Array.prototype.forEach.call( context.querySelectorAll( '[data-kmcb-accordion]' ), function ( root ) {
			if ( root.getAttribute( 'data-kmcb-ready' ) ) {
				return;
			}
			Accordion( root );
		} );

		Array.prototype.forEach.call( context.querySelectorAll( '[data-kmcb-nav]' ), function ( root ) {
			if ( root.getAttribute( 'data-kmcb-ready' ) ) {
				return;
			}
			Navbar( root );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			init( document );
		} );
	} else {
		init( document );
	}

	// Elementor editor / popups re-render markup after load.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		[ 'kmcb_product_grid', 'kmcb_product_gallery', 'kmcb_product_accordion', 'kmcb_faq', 'kmcb_navbar', 'kmcb_product_variations' ].forEach( function ( name ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/' + name + '.default', function ( $scope ) {
				init( $scope && $scope[ 0 ] ? $scope[ 0 ] : document );
			} );
		} );
	} );
} )();
