<?php 

class Revision
{
	public $columns = [];
	public $items = [];
	public $sortable = [];
	public $hidden = [];
	public $table;
	public $totalPages = 2;
	public $perPage = 10;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Setup for the table
		$this->columns = $this->setColumns();
		$this->items = $this->setItems();
		$this->sortable = $this->setSortable();
		$this->columnValues = [];

		// Create the table
		$this->table = new Table([
			'options' => ['singular' => 'Field', 'plural' => 'Fields', 'ajax' => false],
			'columns' => $this->columns,
			'sortable' => $this->sortable,
			'items' => $this->items,
			'hidden' => $this->hidden,
			'columnCB' => 'ID',
			'bulk' => [
				'actions' => [
					'destroy' => __('Delete permantly', 'flaire')
				],
				'methods' => [
					'destroy' => [$this, 'destroy'],
				],
			],
			'columnValues' => [
				'ID' => 'ID',
				'page' => 'post_title',
				'date' => 'post_modified',
				'sizep' => 'sizep',
				'sizem' => 'sizem',
				'total' => 'total',
			],
			'perPage' => $this->perPage,
			'totalItems' => $this->totalItems,
			'totalPages' => $this->totalPages,
		]);
	}

	/**
	 * Set the table columns
	 */
	public function setColumns()
	{
		return [
			'cb' => '<input type="checkbox" />',
			'ID' => __('ID', 'flaire'),
			'page' => __('Page title', 'flaire'),
			'date' => __('Modified', 'flaire'),
			'sizep' => __('Size in posts', 'flaire'),
			'sizem' => __('Size in meta', 'flaire'),
			'total' => __('Total', 'flaire'),
		];
	}

	/**
	 * Set the items
	 */
	public function setItems()
	{
		// Get query class
		global $wpdb;

		// Make the query for orphan results
		$query = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'revision'";

		// Set and get total items per page
		$this->totalItems = $wpdb->query($query);

		// Which page are we
        $paged = !empty($_GET['paged']) ? $_GET['paged'] : '';

        // Set paged when not set
        if(empty($paged) || !is_numeric($paged) || $paged <= 0) $paged = 1;

        $this->totalPages = ceil($this->totalItems / $this->perPage);

        // If we got an paged
        if(!empty($paged) && !empty($this->perPage)) {
        	$offset = ($paged - 1) * $this->perPage;
         	$query .= ' LIMIT ' . (int) $offset . ',' . (int) $this->perPage;
     	}

        // Return the results
        $results = $wpdb->get_results($query);
        foreach ($results as $result) {
        	$sizep = $this->calcPostRowSize($result->ID);
        	$sizem = $this->calcMetaRowSize($result->ID);

        	// Set values
        	$result->sizep = round($sizep, 2) . ' KB';
        	$result->sizem = round($sizem, 2) . ' KB';
        	$result->total = round($sizep + $sizem, 2) . ' KB';
        }

        return $results;
	}

	/**
	 * Set sortable columns
	 */
	public function setSortable()
	{
		return [
			'ID' => ['id', true],
            'Page' => ['page', true]
		];
	}

	/**
	 * Calculate the post row size
	 *
	 * @param  integer $id
	 * @return string
	 */
	public function calcPostRowSize($id)
	{
		// Get query class
		global $wpdb;

		$size = "SELECT (sum(char_length(ID) + char_length(post_author) + char_length(post_date)
     	+ char_length(post_content) + char_length(post_title) + char_length(post_excerpt) + char_length(post_status)
     	+ char_length(comment_status) + char_length(ping_status) + char_length(post_name) + char_length(post_modified)
     	+ char_length(post_modified_gmt) + char_length(guid) + char_length(post_type))) / 1024 
     AS size FROM {$wpdb->prefix}posts WHERE ID = $id";

     	return $wpdb->get_row($size)->size;
	}

	/**
	 * Calculate the meta row size
	 *
	 * @param  integer $id
	 * @return string
	 */
	public function calcMetaRowSize($id)
	{
		// Get query class
		global $wpdb;

		$size = "SELECT (sum(char_length(meta_id) + char_length(post_id) + char_length(meta_key) + char_length(meta_value))) / 1024 AS size FROM {$wpdb->prefix}postmeta  WHERE post_id = $id";
		return $wpdb->get_row($size)->size;	
	}

	/**
	 * Destroy the revision(s)
	 *
	 * @param  array $data
	 * @return boolean
	 */
	public function destroy($data)
	{
		// Check if we got IDS
		if (array_key_exists('ID', $data)) {
			$ids = implode( ',', array_map('absint', $data['ID']));
			$wpdb->query( "DELETE FROM wp_posts WHERE ID IN($ids)");
		}

		// // Delete the items
		// foreach ($data['ID'] as $value) {
		// }
	}
}