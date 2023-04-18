<?php 
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WooZoneNoAwsSyncWidgetList extends WP_List_Table {

	private $the_plugin = null;
	private $displayInterface = null;


	public $bulk_country = 'all';
	public $bulk_status = 'all';



	/** Class constructor */
	public function __construct( $plugin, $interface ) {

		global $status, $page;

		$this->the_plugin = $plugin;
		$this->displayInterface = $interface;


		//Set parent defaults
		parent::__construct(array(
			'singular' => 'asin', //singular name of the listed records
			'plural' => 'ASIN items', //plural name of the listed records
			'ajax' => false        //does this table support ajax?
		));

		$this->bulk_country = isset($_GET['bulk_country']) ? $_GET['bulk_country'] : 'all';
		$this->bulk_status = isset($_GET['bulk_status']) ? $_GET['bulk_status'] : 'all';
	}


	/**
	 * Retrieve syncs data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_syncs( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}amz_sync_widget WHERE 1=1";

		if( $this->bulk_country != "all" ){
			$sql .= ' AND country = "' . ( $this->bulk_country ) . '" ';
		}
		if( $this->bulk_status != "all" ){
			$sql .= ' AND status = "' . ( $this->bulk_status ) . '" ';
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}
		else {
			$sql .= ' ORDER BY ID ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}amz_sync_widget WHERE 1=1";

		if( $this->bulk_country != "all" ){
			$sql .= ' AND country = "' . ( $this->bulk_country ) . '" ';
		}
		if( $this->bulk_status != "all" ){
			$sql .= ' AND status = "' . ( $this->bulk_status ) . '" ';
		}

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no sync data is available */
	public function no_items() {
		_e( 'No syncs avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'country':
				return $item[ $column_name ];

			case 'status':
				$html = array();
				$html[] = '<div class="' . ( WooZone()->alias ) . '-bulk-status ' . $item[ $column_name ] . '">';
				$html[] = 	$item[ $column_name ];
				$html[] = '</div>';
				return implode(PHP_EOL, $html);

			case 'bulk_asins':
				$data = maybe_unserialize( $item[ $column_name ] );
				return '<div class="' . ( WooZone()->alias ) . '-view-all-asin">
					<div>' . implode(",", $data) . '</div>
					' . ( count($data) > 20 ? '<a href="#">view all ' . ( count($data) ) . ' codes</a>' : '' ) . '
				</div>';

			case 'widget_response_date':
			case 'created_date':
				$date = $item[ $column_name ];
				if ( ! empty($date) ) {
					$date = WooZone()->last_update_date('true', strtotime($date));
				}
				return $date;

			case 'action':
				return '<button type="button" class="is-button is-default is-large ' . ( WooZone()->alias ) . '-sync-now">Sync Now</button>';

			case 'status_msg':
				$data = maybe_unserialize( $item[ $column_name ] );
				$msg = isset($data['msg_full']) && ! empty($data['msg_full']) ? $data['msg_full'] : (isset($data['msg']) ? $data['msg'] : '');
				$html = array();
				$html[] = '<div class="' . ( WooZone()->alias ) . '-view-all-statusmsg">';
				$html[] = 	'<div>';
				$html[] = 		$msg;
				$html[] = 	'</div>';
				$html[] = 	! empty($msg) ? '<a href="#">view all details</a>' : '';
				$html[] = '</div>';
				return implode(PHP_EOL, $html);

			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}

	function column_ID( $item ) {
		return sprintf(
			'%s<input type="hidden" name="bulk-id[]" value="%s" />', $item['ID'], $item['ID']
		);
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			//'cb' => '<input type="checkbox" />',
			'ID' => __('ID'),
			'bulk_asins' => __('Bulk asins'),
			'action' => __('Action'),
			'status' => __('Status'),
			'status_msg' => __('Status Details'),
			'country' => __('Country'),
			'widget_response_date' => __('Response date'),
			'created_date' => __('Created date'),
		);
		return $columns;
	}

	protected function display_tablenav( $which )
	{
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}

		if ( 'top' === $which ) {
			echo $this->displayInterface->sync_stats();
		}
	?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
		 
			<?php if ( $this->has_items() ) : ?>
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
				<?php
			endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
		 
			<br class="clear" />
		</div>
	<?php
	}

	protected function extra_tablenav( $which )
	{
		if ( 'top' !== $which ) {
			return '';
		}

		$html = array();
		$html[] = '<div class="' . ( WooZone()->alias ) . '-filter-by">Filter by : </div>';

		// filter by country
		$countries = $this->_get_asin_countries();
		if( $countries && count($countries) ){
			$html[] = '<div class="' . ( WooZone()->alias ) . '-filter-by">';
			//$html[] = 	'<label>Show bulks only for country:</label>';
			$html[] = 	'<select name="filter-by-country">';
			$html[] = 	'<option value="all" disabled="disabled">Filter by country</option>';
			$html[] = 	'<option value="all" ' . ( $this->bulk_country == 'all' ? 'selected' : '' ) . '>All</option>';	
			foreach ($countries as $bulk) {
				$html[] = '<option ' . ( $this->bulk_country == $bulk['country'] ? 'selected' : '' ) . ' value="' . ( $bulk['country'] ) . '">amazon.' . ( $bulk['country'] . ' (' . $bulk['nb'] . ' bulks)' ) . '</option>';
			}
			$html[] = 	'</select>';
			$html[] = '</div>';
		}

		// filter by status
		$statuses = $this->_get_statuses();
		if( $statuses && count($statuses) ){
			$html[] = '<div class="' . ( WooZone()->alias ) . '-filter-by">';
			//$html[] = 	'<label>Show bulks only for status:</label>';
			$html[] = 	'<select name="filter-by-status">';
			$html[] = 	'<option value="all" disabled="disabled">Filter by status</option>';
			$html[] = 	'<option value="all" ' . ( $this->bulk_status == 'all' ? 'selected' : '' ) . '>All</option>';	
			foreach ($statuses as $bulk) {
				$html[] = '<option ' . ( $this->bulk_status == $bulk['status'] ? 'selected' : '' ) . ' value="' . ( $bulk['status'] ) . '">' . ( $bulk['status'] . ' (' . $bulk['nb'] . ' bulks)' ) . '</option>';
			}
			$html[] = 	'</select>';
			$html[] = '</div>';
		}

		$html[] = '<div class="' . ( WooZone()->alias ) . '-filter-by">';
		$html[] = 	'<input type="button" id="filter-by-button" value="Filter" />';
		$html[] = '</div>';

		echo implode( "\n", $html );
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		//$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'syncs_per_page', 15 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		$this->items = $this->get_syncs( $per_page, $current_page );

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );
	}

	private function _get_asin_countries()
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) as nb, country FROM {$wpdb->prefix}amz_sync_widget GROUP BY country ORDER BY nb DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	private function _get_statuses()
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) as nb, status FROM {$wpdb->prefix}amz_sync_widget GROUP BY status ORDER BY nb DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}