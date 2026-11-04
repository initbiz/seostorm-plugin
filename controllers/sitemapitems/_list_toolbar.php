<div data-control="toolbar loader-container">
    <button
        class="btn btn-primary"
        data-request="onRefresh"
        data-request-message="<?= __("Refreshing...") ?>"
        data-request-confirm="<?= __("Are you sure?") ?>"
        >
        <i class="icon-refresh"></i>
        <?= e(trans('initbiz.seostorm::lang.form.settings.btn_refresh_sitemapitems')) ?>
    </button>

    <button
        class="btn btn-secondary"
        data-request="onDelete"
        data-request-message="<?= __("Deleting...") ?>"
        data-request-confirm="<?= __("Are you sure?") ?>"
        data-list-checked-trigger
        data-list-checked-request
        disabled>
        <i class="icon-delete"></i>
        <?= __("Delete") ?>
    </button>

    <a
        href="<?= Backend::url('initbiz/seostorm/sitemapmedia') ?>"
        class="btn btn-secondary oc-icon-clone">
        <?= trans("initbiz.seostorm::lang.models.sitemap_item.btn_see_media") ?>
    </a>
</div>
