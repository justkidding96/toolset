<?php 

class Orphan
{
	public $columns = [];
	public $items = [];
	public $sortable = [];
	public $hidden = [];
	public $table;
	public $totalPages = 2;
	public $perPage = 20;

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
			'columnCB' => 'meta_id',
			'bulk' => [
				'actions' => [
					'destroy' => __('Delete permantly', 'flaire')
				],
				'methods' => [
					'destroy' => [$this, 'destroy'],
				],
			],
			'columnValues' => [
				'ID' => 'meta_id',
				'post' => 'post_id',
				'value' => 'meta_key',
				'field' => 'meta_value',
				'size' => 'size',
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
			'ID' => __('Meta ID', 'flaire'),
			'post' => __('Post ID', 'flaire'),
			'value' => __('Field key', 'flaire'),
			'field' => __('Field value', 'flaire'),
			'size' => __('Size', 'flaire'),
		];
	}

	/**
	 * Set the items
	 */
	public function setItems()
	{
		// Get query class
		global $wpdb;

		$orphans = [];
		$acfFields = $wpdb->get_results("SELECT ID, post_name, post_excerpt FROM wp_posts WHERE post_type = 'acf-field' AND post_excerpt != 'content'");

		$totalItems = 0;

		// Loop through the acf fields
		foreach ($acfFields as $acfField) {
			// Get the fields
			$metaFields = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE meta_key NOT LIKE '%" . $acfField->post_excerpt . "%' AND meta_value = '{$acfField->post_name}'");

			// Loop through the meta fields
			foreach ($metaFields as $metaField) {
				$metaField->size = round($this->calcMetaRowSize($metaField->meta_id), 2) . ' KB';
				$orphans[] = $metaField;

				// Prepare meta key
				$metaKey = ltrim($metaField->meta_key, '_');

				// Get none linked items
				if ($noneLinked = $wpdb->get_row("SELECT * FROM wp_postmeta WHERE meta_key = '{$metaKey}' AND post_id = '{$metaField->post_id}'")) {
					$noneLinked->size = round($this->calcMetaRowSize($metaField->meta_id), 2) . ' KB';
					$orphans[] = $noneLinked;
					$totalItems++;
				}

				$totalItems++;
			}
		}

		$this->totalItems = $totalItems;
		$this->totalPages = ceil($this->totalItems / $this->perPage);
		 
		return $orphans;

		// // Set and get total items per page
		// $this->totalItems = $wpdb->query($query);

		// // Which page are we
  //       $paged = !empty($_GET['paged']) ? $_GET['paged'] : '';

  //       // Set paged when not set
  //       if(empty($paged) || !is_numeric($paged) || $paged <= 0) $paged = 1;

  //       $this->totalPages = ceil($this->totalItems / $this->perPage);

  //       // If we got an paged
  //       if(!empty($paged) && !empty($this->perPage)) {
  //       	$offset = ($paged - 1) * $this->perPage;
  //        	$query .= ' LIMIT ' . (int) $offset . ',' . (int) $this->perPage;
  //    	}
        


        // foreach ($results as $result) {
        // 	$sizep = $this->calcPostRowSize($result->ID);
        // 	$sizem = $this->calcMetaRowSize($result->ID);

        // 	// Set values
        // 	$result->sizep = round($sizep, 2) . ' KB';
        // 	$result->sizem = round($sizem, 2) . ' KB';
        // 	$result->total = round($sizep + $sizem, 2) . ' KB';
        // }

        return $fields;
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
	 * Calculate the meta row size
	 *
	 * @param  integer $id
	 * @return string
	 */
	public function calcMetaRowSize($id)
	{
		// Get query class
		global $wpdb;

		$size = "SELECT (sum(char_length(meta_id)) + sum(char_length(post_id)) + sum(char_length(meta_key)) + sum(char_length(meta_value))) / 1024 AS size FROM {$wpdb->prefix}postmeta  WHERE meta_id = $id";
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
		// Get query class
		global $wpdb;

		// Check if we got IDS
		if (array_key_exists('meta_id', $data)) {
			$ids = implode( ',', array_map('absint', $data['meta_id']));
			$wpdb->query("DELETE FROM wp_postmeta WHERE meta_id IN($ids)");

			// Refresh
			echo "<meta http-equiv='refresh' content='0'>";
		}
	}
}