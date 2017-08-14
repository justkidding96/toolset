<div class="wrap">
    <h2><?php _e('Orphan fields', 'ng_toolset'); ?></h2>

    <form method="post">
        <input type="hidden" name="page" value="list_table">
        <?php
            $revisions = new Orphan;
            $revisions->table->screen = get_current_screen();
            $revisions->table->prepare_items();
            $revisions->table->search_box('search', 'search_id');
            $revisions->table->display();
        ?>
    </form>
</div>