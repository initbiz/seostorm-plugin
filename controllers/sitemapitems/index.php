<?php Block::put('breadcrumb') ?>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= Backend::url('system/settings') ?>"><?= __("Settings") ?></a></li>
    <li class="breadcrumb-item active" aria-current="page">Sitemap Items</li>
</ol>
<?php Block::endPut() ?>

<?= $this->listRender() ?>
