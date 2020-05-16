<?php
/**
 * Created by PhpStorm.
 * User: prashantsingh
 * Date: 16/03/20
 * Time: 3:18 PM
 */
?>
<section class="svrsection">
    <div class="container">
        <?php if (isset($success_msg) && !empty($success_msg)) { ?>
            <div class="col-xs-12">
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            </div>
        <?php } ?>
        <?php if (isset($error_msg) && !empty($error_msg)) { ?>
            <div class="col-xs-12">
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-8 head">
                <h2>Positions List</h2>
            </div>
            <!-- Import link -->
            <div class="col-md-4 head">
                <div class="float-right">
                    <a href="javascript:void(0);" class="btn btn-success" onclick="formToggle('importFrm');"><i
                                class="plus"></i> Import</a>
                </div>
            </div>

            <!-- File upload form -->
            <div class="col-12 col-md-6 col-lg-6" id="importFrm" style="display: none;">
                <form action="<?php echo __BASE_URL__ . '/import_positions'; ?>" method="post"
                      enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="shop_owner_email">Positions CSV:</label>
                        <input type="file" class="form-control" name="file"/>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary col-12" name="importSubmit" value="IMPORT">
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive mt-3">
                    <!-- Data list table -->
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Position</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($positionsArr)) {
                            $i = 1;
                            foreach ($positionsArr as $row) { ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                </tr>
                                <?php $i++;
                            }
                        } else { ?>
                            <tr>
                                <td colspan="2">No position found...</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>