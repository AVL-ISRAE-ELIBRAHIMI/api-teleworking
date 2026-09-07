<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Nouvelle demande de matériel</title>
</head>

<body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
       
        <tr>
            <td style="padding:20px; color:#333;">
                <b style="color:red;">FOR TEST ONLY</b>
                <p>Bonjour <strong><?php echo e($responsable->name); ?></strong>,</p>

                <p>Une nouvelle demande de matériel a été créée :</p>

                <table width="100%" cellpadding="8" cellspacing="0" style="border:1px solid #ddd; border-radius:6px; margin-top:10px;">
                    <tr style="background:#f9f9f9;">
                        <td><strong>Matériel</strong></td>
                        <td><?php echo e($materiel->label); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Projet</strong></td>
                        <td><?php echo e($project->code_projet); ?></td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td><strong>Demandeur</strong></td>
                        <td><?php echo e($requestor->nom); ?> <?php echo e($requestor->prenom); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Date de début</strong></td>
                        <td><?php echo e($requestAsset->borrow_date); ?></td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td><strong>Date de retour</strong></td>
                        <td><?php echo e($requestAsset->return_date); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Lieu prévu</strong></td>
                        <td><?php echo e($requestAsset->new_location); ?></td>
                    </tr>
                </table>
                <div style="text-align:center; margin-top:22px;">
                    <a href="<?php echo e($requestUrl); ?>" style="display:inline-block; background:#4caf50; color:#ffffff; text-decoration:none; padding:12px 28px; border-radius:6px; font-weight:bold; font-size:14px;">
                        Voir la demande
                    </a>
                </div>
            </td>
        </tr>
        <tr>
            <td style=" color:#0d3b66; text-align:center; padding:15px;">
                <small>&copy; <?php echo e(date('Y')); ?> AVL TOOLS CENTER</small>
            </td>
        </tr>
    </table>

</body>

</html><?php /**PATH C:\Avamar\Documents\GitHub\teleworking-tool\api-teleworking\resources\views/emails/NewRequestNotification.blade.php ENDPATH**/ ?>