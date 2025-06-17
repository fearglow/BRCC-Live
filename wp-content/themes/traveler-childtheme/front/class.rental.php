<?php
/**
 * @package    WordPress
 * @subpackage Traveler
 * @since      1.0
 *
 * Class STRental
 *
 * Created by ShineTheme
 */
if ( ! class_exists( 'STRentalNew' ) ) {
	class STRentalNew {

		function __construct() {
			add_action( 'wp_loaded', [ $this, 'after_wp_is_loaded' ] );
		}

		function after_wp_is_loaded() {
			// Query page search
			remove_all_actions( 'wp_ajax_st_filter_rental_ajax' );
			remove_all_actions( 'wp_ajax_nopriv_st_filter_rental_ajax' );
			add_action( 'wp_ajax_st_filter_rental_ajax', [ $this, 'st_filter_rental_ajax_new' ] );
			add_action( 'wp_ajax_nopriv_st_filter_rental_ajax', [ $this, 'st_filter_rental_ajax_new' ] );
			// add_filter( 'posts_where', [ $this, '_get_where_query' ] );
		}

		public function st_filter_rental_ajax_new() {
			$page_number   = STInput::get( 'page' );
			$style         = STInput::get( 'layout' );
			$format        = STInput::get( 'format' );
			$is_popup_map  = STInput::get( 'is_popup_map' );
			$half_map_show = STInput::get( 'half_map_show' );
			$version       = STInput::get( 'version' );
			$agency        = STInput::get( 'agency' );

			if ( empty( $half_map_show ) ) {
				$half_map_show = 'yes';
			}
			$popup_map = '';
			if ( $is_popup_map ) {
				if ( function_exists( 'check_using_elementor' ) && check_using_elementor() ) {
					$popup_map = '<div class="row service-list-wrapper st-scrollbar list-style">';
				} else {
					$popup_map = '<div class="row list-style">';
				}
			}
			if ( ! in_array( $format, [ 'normal', 'halfmap', 'popupmap' ] ) ) {
				$format = 'normal';
			}
			global $wp_query, $st_search_query, $post;
			$old_post = $post;
			$this->setQueryRentalSearch();
			$query = $st_search_query;
			// Map
			$map_lat_center = 0;
			$map_lng_center = 0;
			if ( STInput::request( 'location_id' ) ) {
				$map_lat_center = get_post_meta( STInput::request( 'location_id' ), 'map_lat', true );
				$map_lng_center = get_post_meta( STInput::request( 'location_id' ), 'map_lng', true );
			}
			$data_map = [];
			$stt      = 0;
			ob_start();
			echo st()->load_template( 'layouts/modern/common/loader', 'content' );
			if ( ! isset( $style ) ) {
				$style = 'grid';
			}
			$class_row = '';
			if ( function_exists( 'check_using_elementor' ) && check_using_elementor() ) {
				$class_row = ' service-list-wrapper';
			}
			switch ( $format ) {
				case 'halfmap':
					echo ( $style == 'grid' ) ? '<div class="row' . esc_attr( $class_row ) . '">' : '<div class="row' . esc_attr( $class_row ) . ' list-style">';

					break;
				default:
					if ( function_exists( 'check_using_elementor' ) && check_using_elementor() ) {
						if ( $version == 'elementorv2' ) {
							if ( in_array( 'traveler-layout-essential-for-elementor/traveler-layout-essential-for-elementor.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ( $agency == 'agency' ) ) {
								echo '<div class="row service-list-wrapper">';
							} else {
								echo ( $style == 'grid' ) ? '<div class="row service-list-wrapper rental-grid service-tour">' : '<div class="rental-grid service-tour list-style">';
							}
						} else {
							echo ( $style == 'grid' ) ? '<div class="row service-list-wrapper">' : '<div class="service-list-wrapper list-style">';
						}
					} else {
						echo ( $style == 'grid' ) ? '<div class="row row-wrapper">' : '<div class="style-list">';
					}

					break;
			}
			$isNew                    = false;
			$layoutRequest            = STInput::request( 'version_layout' );
			$formatRequest            = STInput::request( 'version_format' );
			$st_item_row              = STInput::request( 'st_item_row' );
			$st_item_row_tablet       = STInput::request( 'st_item_row_tablet' );
			$st_item_row_tablet_extra = STInput::request( 'st_item_row_tablet_extra' );
			if ( in_array( $layoutRequest, [ '4', '5' ] ) && in_array( $formatRequest, [ 'halfmap', 'popup' ] ) ) {
				$isNew = true;
			}
			// vv( $query->request );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					if ( $format == 'halfmap' ) {
						if ( function_exists( 'check_using_elementor' ) && check_using_elementor() ) {
							if ( $version == 'elementorv2' ) {
								$classes = '';
								if ( $layoutRequest == 4 ) {
									if ( $style == 'list' ) {
										$classes = 'col-12 item-service';
									} else {
										$classes = 'col-12 col-md-6 col-lg-6 item-service';
									}
								} elseif ( ! empty( $st_item_row ) ) {
									$classes = ' item-service col-12' . ' col-sm-' . ( 12 / $st_item_row_tablet ) . ' col-md-' . ( 12 / $st_item_row_tablet_extra ) . ' col-lg-' . ( 12 / $st_item_row ) . ' col-xl-' . ( 12 / $st_item_row );
								} elseif ( $layoutRequest == 5 && $style == 'list' ) {
									$classes = 'col-12 item-service';
								} else {
									$classes = 'col-12 col-md-3 col-lg-3 item-service';
								}
								echo '<div class="' . esc_attr( $classes ) . '">';
								if ( $layoutRequest == 4 && $style == 'list' ) {
									$changeLayout = STInput::request( 'change_layout_to' );
									if ( ! empty( $changeLayout ) ) {
										echo stt_elementorv2()->loadView( 'services/rental/loop/' . $changeLayout );
									} else {
										echo stt_elementorv2()->loadView( 'services/rental/loop/list-2' );
									}
								} else {
									echo stt_elementorv2()->loadView( 'services/rental/loop/' . $style );
								}
								echo '</div>';
							} elseif ( $half_map_show == 'yes' ) {
								if ( $style === 'grid' ) {
									echo st()->load_template( 'layouts/elementor/rental/loop/normal', $style, [ 'item_row' => 2 ] );
								} else {
									echo st()->load_template( 'layouts/modern/rental/elements/loop/halfmap', $style, [ 'item_row' => 1 ] );
								}
							} elseif ( $style === 'grid' ) {
								echo st()->load_template( 'layouts/elementor/rental/loop/normal', $style, [ 'item_row' => 4 ] );
							} else {
								echo st()->load_template( 'layouts/elementor/rental/loop/halfmap', $style, [
									'item_row' => 2,
									'show_map' => $half_map_show,
								] );
							}
						} else {
							if ( $style == 'grid' ) {
								if ( $half_map_show == 'yes' ) {
									echo '<div class="col-lg-6 col-md-6 col-xs-12  ">';
								} else {
									echo '<div class="col-lg-3 col-md-4 col-xs-12 ">';
								}
								echo st()->load_template( 'layouts/modern/rental/elements/loop/normal-grid' );
							} else {
								if ( $half_map_show == 'yes' ) {
									echo '<div class="col-xs-12">';
								} else {
									echo '<div class="col-xs-12 col-md-6">';
								}
								echo st()->load_template( 'layouts/modern/rental/elements/loop/halfmap', $style, [ 'show_map' => $half_map_show ] );
							}
							echo '</div>';
						}
					} elseif ( function_exists( 'check_using_elementor' ) && check_using_elementor() ) {

						if ( $version == 'elementorv2' ) {
							if ( in_array( 'traveler-layout-essential-for-elementor/traveler-layout-essential-for-elementor.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ( $agency == 'agency' ) ) {
								if ( $style === 'grid' ) {
									$col_classes = 'col-lg-12';
									if ( $st_item_row ) {
										$col_classes = 'col-12 col-sm-' . ( 12 / $st_item_row_tablet ) . ' col-md-' . ( 12 / $st_item_row_tablet_extra ) . ' col-lg-' . ( 12 / $st_item_row );
									}
									echo '<div class="' . esc_attr( $col_classes ) . '">';
									echo ste_loadTemplate( 'list-service-rental/rental/loop/grid' );
									echo '</div>';
								} else {
									echo '<div class="col-lg-12">';
									echo ste_loadTemplate( 'list-service-rental/rental/loop/list' );
									echo '</div>';
								}
							} else {

								if ( ! empty( $st_item_row ) ) {
									$classes = 'item-service col-12' . ' col-sm-' . ( 12 / $st_item_row_tablet ) . ' col-md-' . ( 12 / $st_item_row_tablet_extra ) . ' col-lg-' . ( 12 / $st_item_row ) . ' col-xl-' . ( 12 / $st_item_row );
								} elseif ( $style === 'grid' ) {
										$classes = 'col-12 col-md-6 col-lg-4 item-service';
								} else {
									$classes = 'col-12 item-service';
								}
								echo '<div class="' . esc_attr( $classes ) . '">';
								echo stt_elementorv2()->loadView( 'services/rental/loop/' . $style );
								echo '</div>';
							}
						} else {
							echo st()->load_template( 'layouts/elementor/rental/loop/normal', $style, [ 'item_row' => 3 ] );
						}
					} else {
						if ( $style == 'grid' ) {
							echo '<div class="col-lg-4 col-md-6 col-xs-12 ">';
						}
						echo st()->load_template( 'layouts/modern/rental/elements/loop/normal', $style, [ 'show_map' => $half_map_show ] );
						if ( $style == 'grid' ) {
							echo '</div>';
						}
					}
					if ( $is_popup_map ) {
						if ( function_exists( 'check_using_elementor' ) && check_using_elementor() ) {
							$popup_map .= st()->load_template( 'layouts/elementor/rental/loop/halfmap-list' );
						} else {
							$popup_map .= '<div class="col-lg-6 col-md-6 col-xs-12 ">';
							$popup_map .= st()->load_template( 'layouts/modern/rental/elements/loop/popupmap' );
							$popup_map .= '</div>';
						}
					}
					// Map
					$map_lat = get_post_meta( get_the_ID(), 'map_lat', true );
					$map_lng = get_post_meta( get_the_ID(), 'map_lng', true );
					if ( ! empty( $map_lat ) and ! empty( $map_lng ) ) {
						if ( empty( $map_lat_center ) ) {
							$map_lat_center = $map_lat;
						}
						if ( empty( $map_lng_center ) ) {
							$map_lng_center = $map_lng;
						}
						$post_type                     = get_post_type();
						$data_map[ $stt ]['id']        = get_the_ID();
						$data_map[ $stt ]['name']      = get_the_title();
						$data_map[ $stt ]['post_type'] = $post_type;
						$data_map[ $stt ]['lat']       = $map_lat;
						$data_map[ $stt ]['lng']       = $map_lng;
						$post_type_name                = get_post_type_object( $post_type );
						$post_type_name->label;
						if ( $isNew ) {
							$data_map[ $stt ]['content_html'] = preg_replace( '/^\s+|\n|\r|\s+$/m', '', stt_elementorv2()->loadView( 'services/rental/components/map-popup' ) );
						} else {
							$data_map[ $stt ]['content_html'] = preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/rental/elements/content/map-popup' ) );
						}

						$data_map[ $stt ]['content_adv_html'] = preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'vc-elements/st-list-map/loop-adv/rental', false, [ 'post_type' => $post_type_name->label ] ) );
						++$stt;
					}
				}
			} else {
				if ( $is_popup_map ) {
					$popup_map .= '<div class="col-xs-12">' . st()->load_template( 'layouts/modern/rental/elements/none' ) . '</div>';
				}
				echo ( $style == 'grid' ) ? '<div class="col-xs-12">' : '';
				echo st()->load_template( 'layouts/modern/rental/elements/none' );
				echo '</div>';
			}
			echo '</div>';

			if ( in_array( 'traveler-layout-essential-for-elementor/traveler-layout-essential-for-elementor.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ( $agency == 'agency' ) ) {
				$location_id = STInput::request( 'location_id' );
				?>
					<div class="panigation-list-new-style pagination moderm-pagination"
						data-action_service="st_filter_rental_ajax"
						data-st_location_id="<?php echo esc_attr( $location_id ); ?>"
						data-layout="<?php echo esc_attr( $style ) ?>"
						data-st_item_row = "<?php echo esc_attr( $st_item_row ); ?>"
						data-st_item_row_tablet = "<?php echo esc_attr( $st_item_row_tablet ); ?>"
						data-st_item_row_tablet_extra = "<?php echo esc_attr( $st_item_row_tablet_extra ); ?>"
					>
						<?php echo TravelHelper::paging( $query, false ); ?>
					</div>
				<?php
			}

			$ajax_filter_content = ob_get_contents();
			ob_clean();
			ob_end_flush();
			if ( $is_popup_map ) {
				$popup_map .= '</div>';
			}
			ob_start();
			TravelHelper::paging( false, false );
			?>
			<span class="count-string">
				<?php
				if ( ! empty( $st_search_query ) ) {
					$wp_query = $st_search_query;
				}
				if ( $wp_query->found_posts ) :
					$page           = get_query_var( 'paged' );
					$posts_per_page = get_query_var( 'posts_per_page' );
					if ( ! $page ) {
						$page = 1;
					}
					$last = $posts_per_page * ( $page );
					if ( $last > $wp_query->found_posts ) {
						$last = $wp_query->found_posts;
					}
					echo sprintf( __( '%1$d - %2$d of %3$d ', 'traveler' ), $posts_per_page * ( $page - 1 ) + 1, $last, $wp_query->found_posts );
					echo ( $wp_query->found_posts == 1 ) ? __( 'Rental', 'traveler' ) : __( 'Rentals', 'traveler' );
				endif;
				?>
			</span>
			<?php
			$ajax_filter_pag = ob_get_contents();
			ob_clean();
			ob_end_flush();
			$count = balanceTags( $this->get_result_string() ) . '<div id="btn-clear-filter" class="btn-clear-filter" style="display: none;">' . __( 'Clear filter', 'traveler' ) . '</div>';
			// Map
			$map_icon = st()->get_option( 'st_rental_icon_map_marker', '' );
			if ( empty( $map_icon ) ) {
				$map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_hotel.png';
			}
			$data_tmp = [
				'data_map'       => $data_map,
				'map_lat_center' => $map_lat_center,
				'map_lng_center' => $map_lng_center,
				'map_icon'       => $map_icon,
			];
			// End map
			$result = [
				'content'       => $ajax_filter_content,
				'pag'           => $ajax_filter_pag,
				'count'         => $count,
				'page'          => $page_number,
				'content_popup' => $popup_map,
				'data_map'      => $data_tmp,
			];
			wp_reset_query();
			wp_reset_postdata();
			$post = $old_post;
			echo json_encode( $result );
			die;
		}

		public function setQueryRentalSearch() {
			$page_number = STInput::get( 'page', 1 );
			global $wp_query, $st_search_query;
			$this->alter_search_query();
			set_query_var( 'paged', $page_number );
			$paged = $page_number;
			$args  = [
				'post_type'   => 'st_rental',
				's'           => '',
				'post_status' => [ 'publish' ],
				'paged'       => $paged,
			];
			if ( ! empty( $_GET['st_order'] ) ) {
				$args['order'] = $_GET['st_order'];
			}
			if ( ! empty( $_GET['st_orderby'] ) ) {
				$args['orderby'] = $_GET['st_orderby'];
			}
			query_posts( $args );
			$st_search_query = $wp_query;
			$this->remove_alter_search_query();
		}
		function alter_search_query() {
			add_action( 'pre_get_posts', [ $this, 'change_search_arg' ] );
			add_filter( 'posts_where', [ $this, '_get_where_query' ] );
			add_filter( 'posts_join', [ $this, '_get_join_query' ] );
			add_filter( 'posts_orderby', [ $this, '_get_order_by_query' ] );
			add_filter( 'posts_fields', [ $this, '_get_select_query' ] );
			add_filter( 'posts_clauses', [ $this, '_get_query_clauses' ] );
		}
		function remove_alter_search_query() {
			remove_action( 'pre_get_posts', [ $this, 'change_search_arg' ] );
			remove_filter( 'posts_where', [ $this, '_get_where_query' ] );
			remove_filter( 'posts_join', [ $this, '_get_join_query' ] );
			remove_filter( 'posts_orderby', [ $this, '_get_order_by_query' ] );
			remove_filter( 'posts_fields', [ $this, '_get_select_query' ] );
			remove_filter( 'posts_clauses', [ $this, '_get_query_clauses' ] );
		}

		function change_search_arg( $query ) {
			$query->set( 'post_status', 'publish' );
			if ( is_admin() && empty( $_REQUEST['is_search_map'] ) && empty( $_REQUEST['is_search_page'] ) ) {
				return $query;
			}
			// }
			/**
			 * Global Search Args used in Element list and map display
			 * @since 1.2.5
			 */
			global $st_search_args;
			if ( ! $st_search_args ) {
				$st_search_args = $_REQUEST;
			}
			$post_type      = get_query_var( 'post_type' );
			$posts_per_page = st()->get_option( 'rental_posts_per_page', 12 );
			if ( ! empty( $_REQUEST['posts_per_page'] ) ) {
				$posts_per_page = $_REQUEST['posts_per_page'];
			}
			$meta_query = [];
			if ( $post_type == 'st_rental' ) {
				$query->set( 'author', '' );
				if ( STInput::get( 'item_name' ) ) {
					$query->set( 's', STInput::get( 'item_name' ) );
				}
				$query->set( 'posts_per_page', $posts_per_page );
				$has_tax_in_element = [];
				if ( is_array( $st_search_args ) ) {
					foreach ( $st_search_args as $key => $val ) {
						if ( strpos( $key, 'taxonomies--' ) === 0 && ! empty( $val ) ) {
							$has_tax_in_element[ $key ] = $val;
						}
					}
				}
				if ( ! empty( $has_tax_in_element ) ) {
					$tax_query = [];
					foreach ( $has_tax_in_element as $tax => $value ) {
						$tax_name = str_replace( 'taxonomies--', '', $tax );
						if ( ! empty( $value ) ) {
							$value       = explode( ',', $value );
							$tax_query[] = [
								'taxonomy' => $tax_name,
								'terms'    => $value,
								'operator' => 'IN',
							];
						}
					}
					if ( ! empty( $tax_query ) ) {
						$query->set( 'tax_query', $tax_query );
					}
				}
				$tax = STInput::get( 'taxonomy' );
				if ( ! empty( $tax ) && is_array( $tax ) ) {
					$tax_query = [];
					foreach ( $tax as $key => $value ) {
						if ( $value ) {
							$value = explode( ',', $value );
							if ( ! empty( $value ) and is_array( $value ) ) {
								foreach ( $value as $k => $v ) {
									if ( ! empty( $v ) ) {
										$ids[] = $v;
									}
								}
							}
							if ( ! empty( $ids ) ) {
								$tax_query[] = [
									'taxonomy' => $key,
									'terms'    => $ids,
									'operator' => 'AND',
								];
							}
							$ids = [];
						}
					}
					$query->set( 'tax_query', $tax_query );
				}
				/**
				 * Post In and Post Order By from Element
				 * @since  1.2.5
				 * @author quandq
				 */
				if ( ! empty( $st_search_args['st_ids'] ) ) {
					$query->set( 'post__in', explode( ',', $st_search_args['st_ids'] ) );
					$query->set( 'orderby', 'post__in' );
				}
				if ( ! empty( $st_search_args['st_orderby'] ) && $st_orderby = $st_search_args['st_orderby'] ) {
					if ( $st_orderby == 'sale' ) {
						$query->set( 'meta_key', 'total_sale_number' );
						$query->set( 'orderby', 'meta_value_num' );
					}
					if ( $st_orderby == 'rate' ) {
						$query->set( 'meta_key', 'rate_review' );
						$query->set( 'orderby', 'meta_value' );
					}
					if ( $st_orderby == 'discount' ) {
						$query->set( 'meta_key', 'discount_rate' );
						$query->set( 'orderby', 'meta_value_num' );
					}
				}
				if ( ! empty( $st_search_args['sort_taxonomy'] ) && $sort_taxonomy = $st_search_args['sort_taxonomy'] ) {
					if ( isset( $st_search_args[ 'id_term_' . $sort_taxonomy ] ) ) {
						$id_term     = $st_search_args[ 'id_term_' . $sort_taxonomy ];
						$tax_query[] = [
							[
								'taxonomy'         => $sort_taxonomy,
								'field'            => 'id',
								'terms'            => explode( ',', $id_term ),
								'include_children' => false,
							],
						];
					}
				}
				if ( ! empty( $meta_query ) ) {
					$query->set( 'meta_query', $meta_query );
				}
				if ( ! empty( $tax_query ) ) {
					$type_filter_option_attribute = st()->get_option( 'type_filter_option_attribute_rental', 'and' );
					$tax_query['relation']        = $type_filter_option_attribute;
					$query->set( 'tax_query', $tax_query );
				}
			}
		}

		function _get_where_query( $where ) {
			if ( ! TravelHelper::checkTableDuplicate( 'st_rental' ) ) {
				return $where;
			}
			global $wpdb, $st_search_args;
			if ( ! $st_search_args ) {
				$st_search_args = $_REQUEST;
			}
			/**
			 * Merge data with element args with search args
			 * @since  1.2.5
			 * @author quandq
			 */
			if ( STInput::request( 'location_id' ) ) {
				$st_search_args['location_id'] = STInput::request( 'location_id' );
			}
			if ( ! empty( $st_search_args['st_location'] ) ) {
				if ( empty( $st_search_args['only_featured_location'] ) || $st_search_args['only_featured_location'] == 'no' ) {
					$st_search_args['location_id'] = $st_search_args['st_location'];
				}
			}
			if ( isset( $st_search_args['location_id'] ) && ! empty( $st_search_args['location_id'] ) ) {
				$location_id = $st_search_args['location_id'];
				$where       = TravelHelper::_st_get_where_location( $location_id, [ 'st_rental' ], $where );
			} elseif ( isset( $_REQUEST['location_name'] ) && ! empty( $_REQUEST['location_name'] ) ) {
				$location_name = STInput::request( 'location_name', '' );
				$ids_location  = TravelerObject::_get_location_by_name( $location_name );
				if ( ! empty( $ids_location ) && is_array( $ids_location ) ) {
					$where .= TravelHelper::_st_get_where_location( $ids_location, [ 'st_rental' ], $where );
				} else {
					$where .= " AND (tb.address LIKE '%{$location_name}%'";
					$where .= " OR {$wpdb->prefix}posts.post_title LIKE '%{$location_name}%')";
				}
			}
			if ( isset( $_REQUEST['item_name'] ) && ! empty( $_REQUEST['item_name'] ) ) {
				$item_name = STInput::request( 'item_name', '' );
				$where    .= " AND {$wpdb->prefix}posts.post_title LIKE '%{$item_name}%'";
			}
			if ( isset( $_REQUEST['item_id'] ) && ! empty( $_REQUEST['item_id'] ) ) {
				$item_id = STInput::request( 'item_id', '' );
				$where  .= " AND ({$wpdb->prefix}posts.ID = '{$item_id}')";
			}
			$check_in = STInput::get( 'start', '' );
			if ( ! empty( $check_in ) ) {
				$check_in = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( $check_in ) ) );
			} else {
				$check_in = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( date( TravelHelper::getDateFormat() ) ) ) );
			}
			$check_out = STInput::get( 'end', '' );
			if ( empty( $check_out ) ) {
				$check_out = date( 'Y-m-d', strtotime( '+1 day', strtotime( $check_in ) ) );
			} else {
				$check_out = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( $check_out ) ) );
			}
			if ( ! empty( $check_in ) && ! empty( $check_out ) ) {
				$today        = date( 'Y-m-d' );
				$period       = STDate::dateDiff( $today, $check_in );
				$adult_number = STInput::get( 'adult_number', 0 );
				if ( intval( $adult_number ) < 0 ) {
					$adult_number = 0;
				}
				$children_number = STInput::get( 'children_num', 0 );
				if ( intval( $children_number ) < 0 ) {
					$children_number = 0;
				}
				$avai_check = st()->get_option( 'rental_availability_check', 'on' );
				if ( $avai_check === 'on' ) {
					$list_rental = $this->get_unavailable_rental( $check_in, $check_out );
					if ( is_array( $list_rental ) && ! empty( $list_rental ) ) {
						$list_rental = implode( ',', $list_rental );
						$where      .= " AND {$wpdb->posts}.ID NOT IN ({$list_rental})";
					}
					$where .= " AND CAST(tb.rentals_booking_period AS UNSIGNED) <= {$period}";
				}
			}
			if ( $stars = STInput::get( 'star_rate' ) ) {
				$stars    = STInput::get( 'star_rate', 1 );
				$stars    = explode( ',', $stars );
				$all_star = [];
				if ( ! empty( $stars ) && is_array( $stars ) ) {
					foreach ( $stars as $val ) {
						$start_range = 0;
						$max_range   = 0;
						if ( $val == 'zero' ) {
							$val         = 0;
							$start_range = $val;
							$max_range   = $val + 1;
						} else {
							$start_range = $val + 0.1;
							$max_range   = $val + 1;
						}
						if ( empty( $all_star ) ) {
							$all_star = range( $start_range, $max_range, 0.1 );
						} else {
							$all_star = array_merge( $all_star, range( $start_range, $max_range, 0.1 ) );
						}
					}
				}
				$list_star = implode( ',', array_unique( $all_star ) );
				if ( $list_star ) {
					$where .= " AND (tb.rate_review IN ({$list_star}))";
				}
			}

			$where .= ' AND (CAST(tb2.number AS DECIMAL) - CAST(tb2.number_booked AS DECIMAL) > 0)';
			if ( $adult_number = STInput::get( 'adult_number' ) ) {
				$where .= " AND tb.rental_max_adult>= {$adult_number}";
			}
			if ( $child_number = STInput::get( 'child_number' ) ) {
				$where .= " AND tb.rental_max_children>= {$child_number}";
			}
			if ( isset( $_REQUEST['range'] ) and isset( $_REQUEST['location_id'] ) ) {
				$range       = STInput::get( 'range', '0;5' );
				$rangeobj    = explode( ';', $range );
				$range_min   = $rangeobj[0];
				$range_max   = $rangeobj[1];
				$location_id = STInput::request( 'location_id' );
				$post_type   = get_query_var( 'post_type' );
				$map_lat     = (float) get_post_meta( $location_id, 'map_lat', true );
				$map_lng     = (float) get_post_meta( $location_id, 'map_lng', true );
				global $wpdb;
				$where .= "
                AND $wpdb->posts.ID IN (
                        SELECT ID FROM (
                            SELECT $wpdb->posts.*,( 6371 * acos( cos( radians({$map_lat}) ) * cos( radians( mt1.meta_value ) ) *
                                            cos( radians( mt2.meta_value ) - radians({$map_lng}) ) + sin( radians({$map_lat}) ) *
                                            sin( radians( mt1.meta_value ) ) ) ) AS distance
                                                FROM $wpdb->posts, $wpdb->postmeta as mt1,$wpdb->postmeta as mt2
                                                WHERE $wpdb->posts.ID = mt1.post_id
                                                and $wpdb->posts.ID=mt2.post_id
                                                AND mt1.meta_key = 'map_lat'
                                                and mt2.meta_key = 'map_lng'
                                                AND $wpdb->posts.post_status = 'publish'
                                                AND $wpdb->posts.post_type = '{$post_type}'
                                                AND $wpdb->posts.post_date < NOW()
                                                GROUP BY $wpdb->posts.ID HAVING distance >= {$range_min} and distance <= {$range_max}
                                                ORDER BY distance ASC
                        ) as st_data
	            )";
			}

			// Query nearby lat/lng
			if ( isset( $_REQUEST['location_lat'] ) && isset( $_REQUEST['location_lng'] ) ) {
				$location_lat      = $_REQUEST['location_lat'];
				$location_lng      = $_REQUEST['location_lng'];
				$location_distance = isset( $_REQUEST['location_distance'] ) ? $_REQUEST['location_distance'] : 500;
				if ( $location_lat && $location_lng ) {
					$where .= " AND $wpdb->posts.ID IN (
                                    SELECT ID FROM (
                                        SELECT $wpdb->posts.*,( 6371 * acos( cos( radians({$location_lat}) ) * cos( radians( mt1.meta_value ) ) *
											cos( radians( mt2.meta_value ) - radians({$location_lng}) ) + sin( radians({$location_lat}) ) *
											sin( radians( mt1.meta_value ) ) ) ) AS distance
												FROM $wpdb->posts, $wpdb->postmeta as mt1,$wpdb->postmeta as mt2
												WHERE $wpdb->posts.ID = mt1.post_id
												and $wpdb->posts.ID=mt2.post_id
												AND mt1.meta_key = 'map_lat'
												and mt2.meta_key = 'map_lng'
												AND $wpdb->posts.post_status = 'publish'
												AND $wpdb->posts.post_type = 'st_rental'
												AND $wpdb->posts.post_date < NOW()
												GROUP BY $wpdb->posts.ID HAVING distance <= {$location_distance}
												ORDER BY distance ASC
                                    ) as st_data
                            )";

				}
			}
			// End query nearby lat/lng

			/**
			 * @since 1.3.1
			 *        Remove search by number of rental room
			 */
			/**
			 * Change Where for Element List
			 * @since  1.2.5
			 * @author quandq
			 */
			if ( ! empty( $st_search_args['only_featured_location'] ) and ! empty( $st_search_args['featured_location'] ) ) {
				$featured = $st_search_args['featured_location'];
				if ( $st_search_args['only_featured_location'] == 'yes' and is_array( $featured ) ) {
					if ( is_array( $featured ) && count( $featured ) ) {
						$where    .= ' AND (';
						$where_tmp = '';
						foreach ( $featured as $item ) {
							if ( empty( $where_tmp ) ) {
								$where_tmp .= " tb.multi_location LIKE '%_{$item}_%'";
							} else {
								$where_tmp .= " OR tb.multi_location LIKE '%_{$item}_%'";
							}
						}
						$featured   = implode( ',', $featured );
						$where_tmp .= " OR tb.id_location IN ({$featured})";
						$where     .= $where_tmp . ')';
					}
				}
			}
			return $where;
		}

		function _get_join_query( $join ) {
			if ( ! TravelHelper::checkTableDuplicate( 'st_rental' ) ) {
				return $join;
			}
			global $wpdb;
			$check_in = STInput::get( 'start', date( TravelHelper::getDateFormat() ) );
			if ( ! empty( $check_in ) ) {
				$check_in = strtotime( TravelHelper::convertDateFormat( $check_in ) );
			} else {
				$check_in = strtotime( TravelHelper::convertDateFormat( date( TravelHelper::getDateFormat() ) ) );
			}
			$check_out = STInput::get( 'end', '' );
			if ( empty( $check_out ) ) {
				$check_out = strtotime( '+1 day', $check_in );
			} else {
				$check_out = strtotime( TravelHelper::convertDateFormat( $check_out ) );
			}

			$table       = $wpdb->prefix . 'st_rental';
			$table_avail = $wpdb->prefix . 'st_rental_availability';
			$join       .= " INNER JOIN {$table} as tb ON {$wpdb->prefix}posts.ID = tb.post_id";
			$join       .= " INNER JOIN
            (
                SELECT tb3.number , tb3.number_booked , tb3.post_id,
					SUM(
						CASE
							WHEN tb.is_sale_schedule != 'on' THEN
								CASE
									WHEN tb.discount_type = 'percent'
										THEN
											CAST(tb3.price AS DECIMAL) -(CAST(tb3.price AS DECIMAL) / 100) * CAST(tb.discount_rate AS DECIMAL)
									ELSE CAST(tb3.price AS DECIMAL) - CAST(tb.discount_rate AS DECIMAL)
								END
							WHEN tb.is_sale_schedule = 'on'THEN
								CASE
									WHEN(UNIX_TIMESTAMP(DATE(tb.sale_price_from)) <= {$check_in} AND UNIX_TIMESTAMP(DATE(tb.sale_price_to)) >= {$check_out})
										THEN
											CASE
												WHEN tb.discount_type = 'percent'
													THEN CAST(tb3.price AS DECIMAL) - (CAST(tb3.price AS DECIMAL) / 100) * CAST(tb.discount_rate AS DECIMAL)
												ELSE CAST(tb3.price AS DECIMAL) - CAST(tb.discount_rate AS DECIMAL)
											END
									ELSE tb3.price
								END
							ELSE tb3.price
						END
					) as st_rental_price
                FROM {$table_avail} AS tb3
				LEFT JOIN {$wpdb->prefix}st_rental AS tb ON tb.post_id = tb3.post_id
                WHERE (
						tb3.check_in >= {$check_in}
						AND tb3.check_out <= {$check_out}
						AND( CAST(tb3.number AS DECIMAL) - CAST(tb3.number_booked AS DECIMAL) > 0 )
					)
					OR
					(
						tb3.check_in >= {$check_in}
						AND tb3.check_out <= {$check_out}
						AND( CAST(tb3.number AS DECIMAL) - CAST(tb3.number_booked AS DECIMAL) > 0 )
						AND tb3.groupday = 1
					)
				GROUP BY tb3.post_id
              ) AS tb2
              ON {$wpdb->prefix}posts.ID = tb2.post_id";
			return $join;
		}

		static function _get_order_by_query( $orderby ) {
			if ( strpos( $orderby, 'FIELD(' ) !== false && ( strpos( $orderby, 'posts.ID' ) !== false ) ) {
				return $orderby;
			}
			if ( $check = STInput::get( 'orderby' ) ) {
				global $wpdb;
				$is_featured = st()->get_option( 'is_featured_search_rental', 'off' );
				if ( ! empty( $is_featured ) && $is_featured == 'on' ) {
					if ( ! empty( STInput::get( 'check_single_location' ) ) && STInput::get( 'check_single_location' ) === 'is_location' ) {
						$orderby = 'tb.is_featured desc';
						$check   = 'is_featured';
					}
				}
				switch ( $check ) {
					case 'price_asc':
						$orderby = ' CAST( tb.sale_price as DECIMAL ) asc';
						break;
					case 'price_desc':
						$orderby = ' CAST( tb.sale_price as DECIMAL ) desc';
						break;
					case 'name_asc':
						$orderby = $wpdb->posts . '.post_title asc';
						break;
					case 'name_desc':
						$orderby = $wpdb->posts . '.post_title desc';
						break;
					case 'rand':
						$orderby = ' rand()';
						break;
					case 'new':
						$orderby = $wpdb->posts . '.post_modified desc';
						break;
					default:
						if ( ! empty( $is_featured ) && $is_featured == 'on' ) {
							$orderby = 'tb.is_featured desc';

						} else {
							$orderby = $orderby;
						}
						break;
				}
			} else {
				global $wpdb, $wp_query;
				if ( empty( $wp_query->query['orderby'] ) ) {
					$is_featured = st()->get_option( 'is_featured_search_rental', 'off' );
					if ( ! empty( $is_featured ) && $is_featured == 'on' ) {
						$orderby = 'tb.is_featured desc, ' . $wpdb->posts . '.post_date desc';
					} else {

					}
				}
			}
			return $orderby;
		}
		function _get_select_query( $query ) {
			if ( STAdminRental::check_ver_working() == false ) {
				return $query;
			}
			$post_type = get_query_var( 'post_type' );
			$check_in  = STInput::get( 'start', date( TravelHelper::getDateFormat() ) );
			if ( ! empty( $check_in ) ) {
				$check_in = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( $check_in ) ) );
			} else {
				$check_in = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( date( TravelHelper::getDateFormat() ) ) ) );
			}
			$check_out = STInput::get( 'end', '' );
			if ( ! empty( $check_out ) ) {
				$check_out = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( $check_out ) ) );
			} else {
				$check_out = date( 'Y-m-d', strtotime( '+1 day', strtotime( $check_in ) ) );
			}
			if ( $post_type == 'st_rental' ) {
				global  $wpdb;

				$table_avail = $wpdb->prefix . 'st_rental_availability';

				$query .= ',
                st_rental_price
                ';
			}
			return $query;
		}
		function _get_query_clauses( $clauses ) {
			if ( STAdminRental::check_ver_working() == false ) {
				return $clauses;
			}
			global $wpdb;
			if ( empty( $clauses['groupby'] ) ) {
				$clauses['groupby'] = $wpdb->posts . '.ID';
			}

			$check_in = STInput::get( 'start', date( TravelHelper::getDateFormat() ) );
			if ( ! empty( $check_in ) ) {
				$check_in = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( $check_in ) ) );
			} else {
				$check_in = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( date( TravelHelper::getDateFormat() ) ) ) );
			}
			$check_out = STInput::get( 'end', '' );
			if ( ! empty( $check_out ) ) {
				$check_out = date( 'Y-m-d', strtotime( TravelHelper::convertDateFormat( $check_out ) ) );
			} else {
				$check_out = date( 'Y-m-d', strtotime( '+1 day', strtotime( $check_in ) ) );
			}
			$numberday = STDate::dateDiff( $check_in, $check_out );

			if ( isset( $_REQUEST['price_range'] ) && isset( $clauses['groupby'] ) ) {
				$price               = STInput::get( 'price_range', '0;0' );
				$priceobj            = explode( ';', $price );
				$priceobj[0]         = TravelHelper::convert_money_to_default( $priceobj[0] );
				$priceobj[1]         = TravelHelper::convert_money_to_default( $priceobj[1] );
				$min_range           = (int) $priceobj[0] * $numberday;
				$max_range           = (int) $priceobj[1] * $numberday;
				$clauses['groupby'] .= " HAVING CAST(st_rental_price AS DECIMAL) >= {$min_range} AND CAST(st_rental_price AS DECIMAL) <= {$max_range}";
			}
			return $clauses;
		}

		function get_result_string() {
			global $wp_query, $st_search_query;
			if ( $st_search_query ) {
				$query = $st_search_query;
			} else {
				$query = $wp_query;
			}
			$result_string = $p1 = $p2 = $p3 = $p4 = '';
			if ( $query->found_posts ) {
				if ( $query->found_posts > 1 ) {
					$p1 = sprintf( __( '%s campsites ', 'traveler' ), $query->found_posts );
				} else {
					$p1 = sprintf( __( '%s campsite ', 'traveler' ), $query->found_posts );
				}
			} else {
				$p1 = __( 'No campsites found', 'traveler' );
			}
			$location_id = STInput::get( 'location_id' );
			if ( $location_id && $location = get_post( $location_id ) ) {
				$p2 = sprintf( __( ' in %s', 'traveler' ), get_the_title( $location_id ) );
			} elseif ( STInput::request( 'location_name' ) ) {
				$p2 = sprintf( __( ' in %s', 'traveler' ), STInput::request( 'location_name' ) );
			} elseif ( STInput::request( 'address' ) ) {
				$p2 = sprintf( __( ' in %s', 'traveler' ), STInput::request( 'address' ) );
			}
			if ( STInput::request( 'st_google_location', '' ) != '' ) {
				$p2 = sprintf( __( ' in %s', 'traveler' ), esc_html( STInput::request( 'st_google_location', '' ) ) );
			}
			$start = TravelHelper::convertDateFormat( STInput::get( 'start' ) );
			$end   = TravelHelper::convertDateFormat( STInput::get( 'end' ) );
			$start = strtotime( $start );
			$end   = strtotime( $end );
			if ( $start && $end ) {
				$p3 = __( ' on ', 'traveler' ) . date_i18n( 'M d', $start ) . ' - ' . date_i18n( 'M d', $end );
			}
			if ( $adult_number = STInput::get( 'adult_number' ) ) {
				if ( $adult_number > 1 ) {
					$p4 = sprintf( __( ' for %s adults', 'traveler' ), $adult_number );
				} else {
					$p4 = sprintf( __( ' for %s adult', 'traveler' ), $adult_number );
				}
			}
			// check Right to left
			if ( st()->get_option( 'right_to_left' ) == 'on' || is_rtl() ) {
				return $p1 . ' ' . $p4 . ' ' . $p3 . ' ' . $p2;
			}
			return esc_html( $p1 . ' ' . $p2 . ' ' . $p3 . ' ' . $p4 );
		}

		function get_unavailable_rental( $check_in, $check_out ) {
			global $wpdb;
			$check_in   = strtotime( $check_in );
			$check_out  = strtotime( $check_out );
			$where_raw  = $wpdb->prepare( '( priority = 1 AND check_in = %s )', $check_in );
			$where_raw2 = $wpdb->prepare( '( check_in > %s AND check_in < %s AND priority IN ("1","2") )', $check_in, $check_out );
			$res       = ST_Rental_Availability::inst()
				->select( 'post_id' )
				->where( 'check_in >=', $check_in )
				->where( 'check_out <=', $check_out )
				->where( "(status = 'unavailable' OR (number - number_booked <= 0))", null, true )
				->where( "( CASE WHEN allow_full_day = 'off' THEN status = 'unavailable' OR priority Not IN ('1','2') ELSE status = 'unavailable' OR (number - number_booked <= 0) END )", null, true )
				->or_where( $where_raw, null, true )
				->or_where( $where_raw2, null, true )
				->groupby( 'post_id' )
				->get()->result();

			$list      = [];
			if ( ! empty( $res ) ) {
				foreach ( $res as $k => $v ) {
					array_push( $list, $v['post_id'] );
				}
			}
			return $list;
		}
	}


	new STRentalNew;
}
