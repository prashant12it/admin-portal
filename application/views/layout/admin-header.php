<?php
/**
 * Created by PhpStorm.
 * User: prashantsingh
 * Date: 16/03/20
 * Time: 3:17 PM
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $szMetaTagTitle.' - '.__SITE_NAME__;?></title>
    <link href="<?php echo __BASE_CSS_URL__; ?>/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo __BASE_CSS_URL__; ?>/style.css?<?php echo time();?>">
    <link rel="stylesheet" href="<?php echo __BASE_CSS_URL__; ?>/formpage-style.css?<?php echo time();?>">
    <link rel="stylesheet" href="<?php echo __BASE_CSS_URL__; ?>/responsive.css?<?php echo time();?>">

    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,500,600,700,800,900&display=swap" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo __BASE_CSS_URL__; ?>/prashant.css?<?php echo time();?>" rel="stylesheet">

</head>

<body class="bg1">

    <section class="titlesection">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <?php if(isset($role) && $role == 1) { ?>
                        <a href="<?php echo __BASE_URL__ . '/logout'; ?>" class="startlink mr-3">Logout</a>
                        <a href="<?php echo __BASE_URL__ . '/jobs'; ?>"
                           class="startlink mr-3">Jobs |</a>
                        <a href="<?php echo __BASE_URL__ . '/positions'; ?>"
                           class="startlink mr-3">Positions |</a>
                        <a href="<?php echo __BASE_URL__ . '/companies'; ?>"
                           class="startlink mr-3">Companies |</a>
                        <a href="<?php echo __BASE_URL__ . '/dashboard'; ?>" class="startlink mr-3">Home |</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>