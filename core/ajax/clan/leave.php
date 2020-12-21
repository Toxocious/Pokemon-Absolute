<?php
  require_once '../../required/layout_top.php';

  $User_Clan = $Clan_Class->FetchClanData($User_Data['Clan']);
?>

<div class='panel content'>
  <div class='head'>Leave <?= $User_Clan['Name']; ?></div>
  <div class='body' style='padding: 5px;'>
    <?php
      if ( !$User_Clan )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must currently be in a clan.
          </div>
        ";
    
        return;
      }
    ?>

    <div class='description'>
      Here, you may leave your clan.
      <br />
      Doing so is permanent, and, to rejoin the clan, you'll have to re-apply to it in the future.
    </div>

    <?php
      if ( isset($_POST['Leave_Clan']) )
      {
        $Leave_Status = $Clan_Class->LeaveClan($User_Data['id']);
        
        if ( $Leave_Status )
        {
          $Leave_Message = "
            <td>
              <div class='success' style='margin-bottom: 0px;'>
                You have successfully left your clan.
              </div>
            </td>
          ";
        }
        else
        {
          $Leave_Message = "
            <td>
              <div class='error' style='margin-bottom: 0px;'>
                An error occurred while processing your input.
              </div>
            </td>
          ";
        }

        echo $Leave_Message;
      }
      else
      {
    ?>

      <table class='border-gradient' style='width: 400px;'>
        <tbody>
          <tr>
            <td colspan='2' style='height: 200px; width: 200px;'>
              <?= ( $User_Clan['Avatar'] ? "<img src='{$User_Clan['Avatar']}' />" : 'This clan has no avatar set.' ); ?>
            </td>
            <td colspan='2' style='height: 200px; width: 200px;'>
              <?= ( $User_Clan['Signature'] ? $User_Clan['Signature'] : 'This clan has no signature set.' ); ?>
            </td>
          </tr>
          <tr>
            <td colspan='4' style='padding: 10px;'>
              <b>Are you sure you wish to leave your clan?</b>
            </td>
          </tr>
          <tr>
            <td colspan='4' style='padding: 10px;'>
              <form method='POST'>
                <input type='submit' name='Leave_Clan' value='Leave Clan' />
              </form>
            </td>
          </tr>
        </tbody>
      </table>

    <?php
      }
    ?>
  </div>
</div>