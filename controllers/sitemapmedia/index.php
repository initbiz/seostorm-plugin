<?php Block::put('breadcrumb') ?>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= Backend::url('system/settings') ?>"><?= __("Settings") ?></a></li>
    <li class="breadcrumb-item">
        <a href="<?= Backend::url('system/settings/update/initbiz/seostorm/settings#primarytab-sitemap') ?>">
            <?= e(trans('initbiz.seostorm::lang.form.settings.btn_back_to_settings')) ?>
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="<?= Backend::url('initbiz/seostorm/sitemapitems') ?>">
            <?= e(trans('initbiz.seostorm::lang.models.sitemap_item.list_label')) ?>
        </a>
    </li>
    <li class="breadcrumb-item active">Sitemap Media</li>
</ol>
<?php Block::endPut() ?>

<?= $this->listRender() ?>
