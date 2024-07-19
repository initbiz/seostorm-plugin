<?php Block::put('breadcrumb') ?>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= Backend::url('system/settings') ?>"><?= __("Settings") ?></a></li>
    <li class="breadcrumb-item"><a href="<?= Backend::url('initbiz/seostorm/sitemapitems') ?>">Sitemap Items</a></li>
    <li class="breadcrumb-item active">Sitemap Media</li>
</ol>
<?php Block::endPut() ?>

<?= $this->listRender() ?>
