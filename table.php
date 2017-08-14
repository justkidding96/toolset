<?php

if (!class_exists('WP_List_Table')) {
   require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Table extends WP_List_Table {
	public $args;

	/**
	 * Constructor
	 */
	public function __construct(array $args)
	{
		// Set the arguments
		$this->args = $args;

		// Construct the parent
		parent::construct($this->args['options']);
	}

	/**
	 * Define table classes
	 *
	 * @return array
	 */
	public function get_table_classes() 
	{
        return ['widefat', 'fixed', 'striped', $this->_args['plural']];
    }

    /**
     * Set the table columns
     *
     * @return array
     */
    public function get_columns() 
    {
    	return $this->args['columns'];
    }

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() 
	{
		return $this->args['sortable'];
	}

	/**
     * Render the checkbox column
     *
     * @param  object
     * @return string
     */
    public function column_cb($item) 
    {
    	$column = $this->args['columnCB'];
        return sprintf('<input type="checkbox" name="' . $column . '[]" value="%d" />', $item->{$column});
    }

    /**
     * Display the data in the table
     *
     * @param  object $item
     * @param  string $name
     * @return string
     */
    public function column_default($item, $name) 
    {
        // Loop through the specificed columns
        foreach ($this->args['columnValues'] as $columnName => $columnValue) {
            if ($name === $columnName) {
                return $item->{$columnValue};
            }
        }

        // switch ($name) {
        //     case 'ID':
        //         return $item->meta_id;
        //     case 'page':
        //         return $item->post->post_title;
        //     case 'type':
        //         return $item->post->post_type;
        //     default:
        //         return isset($item->$name ) ? $item->$name : '';
        // }
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        return $this->args['bulk']['actions'];
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action()
    {
    	// Only if we got an nonce
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            // Check nonce
            if (!wp_verify_nonce( $nonce, $action)) {
                wp_die('Nope! Security check failed!');
            }

            // Get the bulk action
            $action = $this->args['bulk']['methods'][$_POST['action']];
            
            // Call the method
            $action[0]->destroy($_POST);
        }
    }

    /**
     * Prepare items for the table
     */
    public function prepare_items()
    {
    	// Set column headers
    	$this->_column_headers = [$this->args['columns'], $this->args['hidden'], $this->args['sortable']];

    	// Set table items
    	$this->items = $this->args['items'];

        // Process bulk actions
        $this->process_bulk_action();

    	// Set pagination arguments
     	$this->set_pagination_args(array(
        	'total_items' => (int) $this->args['totalItems'],
         	'total_pages' => (int) $this->args['totalPages'],
         	'per_page'    => (int) $this->args['perPage'],
      	));
    }
}