<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuRrssbStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuRrssbStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;

			$this->p->util->add_plugin_filters( $this, array(
				'form_button_rows' => 2,
			), 1000 );
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			$row_num = null;

			switch ( $menu_id ) {

				case 'rrssb-styles':

					$row_num = 0;

					break;

				case 'sso-tools':
				case 'tools':

					$row_num = 2;

					break;
			}

			if ( null !== $row_num ) {
				$form_button_rows[ $row_num ][ 'reload_default_rrssb_styles' ] = _x( 'Reload Default Responsive Styles',
					'submit button', 'wpsso-rrssb' );
			}

			return $form_button_rows;
		}

		/**
		 * Called by the extended WpssoAdmin class.
		 */
		protected function add_meta_boxes() {

			$metabox_id      = 'sharing_styles';
			$metabox_title   = _x( 'Social Sharing Styles', 'metabox title', 'wpsso-rrssb' );
			$metabox_screen  = $this->pagehook;
			$metabox_context = 'normal';
			$metabox_prio    = 'default';
			$callback_args   = array(	// Second argument passed to the callback function / method.
			);

			add_meta_box( $this->pagehook . '_' . $metabox_id, $metabox_title,
				array( $this, 'show_metabox_sharing_styles' ), $metabox_screen,
					$metabox_context, $metabox_prio, $callback_args );
		}

		public function show_metabox_sharing_styles() {

			$metabox_id = 'rrssb_styles';

			if ( file_exists( WpssoRrssbSocial::$sharing_css_file ) && false !== ( $fsize = filesize( WpssoRrssbSocial::$sharing_css_file ) ) ) {
				$css_min_msg = ' <a href="' . WpssoRrssbSocial::$sharing_css_url . '">minified css is ' . $fsize . ' bytes</a>';
			} else {
				$css_min_msg = '';
			}

			$this->p->util->do_metabox_table( array( 

				$this->form->get_th_html( _x( 'Use the Social Stylesheet', 'option label', 'wpsso-rrssb' ), '', 'buttons_use_social_style' ) . 
				'<td>' . $this->form->get_checkbox( 'buttons_use_social_style' ) . $css_min_msg . '</td>',

				$this->form->get_th_html( _x( 'Enqueue the Stylesheet', 'option label', 'wpsso-rrssb' ), '', 'buttons_enqueue_social_style' ) . 
				'<td>' . $this->form->get_checkbox( 'buttons_enqueue_social_style' ) . '</td>',
			) );

			$table_rows  = array();

			$styles_tabs = apply_filters( $this->p->lca . '_' . $metabox_id . '_tabs', $this->p->cf['sharing']['rrssb_styles'] );

			foreach ( $styles_tabs as $tab_key => $title ) {

				$filter_name = $this->p->lca . '_' . $metabox_id . '_' . $tab_key . '_rows';

				$table_rows[ $tab_key ] = array_merge(
					$this->get_table_rows( $metabox_id, $tab_key ), 
					(array) apply_filters( $filter_name, array(), $this->form )
				);

				$styles_tabs[ $tab_key ] = _x( $title, 'metabox tab', 'wpsso-ssb' );	// Translate the tab title.
			}

			$this->p->util->do_metabox_tabbed( $metabox_id, $styles_tabs, $table_rows );
		}

		protected function get_table_rows( $metabox_id, $tab_key ) {

			$table_rows['buttons_css_' . $tab_key] = '<th class="textinfo">' . $this->p->msgs->get( 'info-styles-' . $tab_key ) . '</th>' . 
			'<td' . ( isset( $this->p->options['buttons_css_' . $tab_key . ':is'] ) &&
				$this->p->options['buttons_css_' . $tab_key . ':is'] === 'disabled' ? ' class="blank"' : '' ) . '>' . 
			$this->form->get_textarea( 'buttons_css_' . $tab_key, 'button_css code' ) . '</td>';

			return $table_rows;
		}
	}
}
