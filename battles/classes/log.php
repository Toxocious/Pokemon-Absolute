<?php
  use BattleHandler\Battle;

  class Log extends Battle
  {
    const ACTIONS = [
      'Unknown',
      'Attack',
      'Continue',
      'Restart',
      'Switch',
      'UseItem',
      'Bag',
      'Misclick'
    ];

    public function __construct() { }

    /**
     * Create a new log in the database.
     * Called when $Fight->CreateBattle() is called.
     */
    public function Initialize()
    {
      global $PDO, $User_Data;

      $Client_User_Agent = GetUserAgent();

      try
      {
        $PDO->beginTransaction();

        $Initialize_Battle_Log = $PDO->prepare("
          INSERT INTO battle_logs
          (
            `User_ID`,
            `Foe_ID`,
            `Session_Battle_ID`,
            `Battle_Type`,
            `Battle_Layout`,
            `Time_Battle_Started`,
            `Window_In_Focus`,
            `Client_IP`,
            `Client_User_Agent`
          )
          VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )
        ");
        $Initialize_Battle_Log->execute([
          $_SESSION['Battle']['Ally_ID'],
          $_SESSION['Battle']['Foe_ID'],
          $_SESSION['Battle']['Battle_ID'],
          $_SESSION['Battle']['Battle_Type'],
          empty($_SESSION['Battle']['Battle_Layout']) ? $User_Data['Battle_Theme'] : $_SESSION['Battle']['Battle_Layout'],
          $_SESSION['Battle']['Time_Started'],
          true,
          $_SERVER['REMOTE_ADDR'],
          $Client_User_Agent['User_Agent']
        ]);

        $_SESSION['Battle']['Logging']['Actions'] = [];
        $_SESSION['Battle']['Logging']['Log_ID'] = $PDO->lastInsertId();

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }
    }

    /**
     * When an action is performed, add a new entry to the session log.
     *
     * @param {string} $Action
     *  - The action that was performed.
     *  - (Attack, Continue, Restart, etc.)
     */
    public function AddAction
    (
      $Action
    )
    {
      $Get_Action = array_search($Action, self::ACTIONS);
      if ( !$Get_Action )
        $Get_Action = 0;

      $Action = $Get_Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['Input']['Client_X'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['Input']['Client_Y'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['Input']['Is_Trusted'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['In_Focus'];

      $_SESSION['Battle']['Logging']['Actions'][] = $Action;
    }

    /**
     * Update the current battle log w/ the finalized information.
     */
    public function Finalize()
    {
      global $PDO;

      if ( empty($_SESSION['Battle']['Logging']) )
        return false;

      $_SESSION['Battle']['Last_Action_Time'] = (microtime(true) - $_SESSION['Battle']['Time_Started']) * 1000;

      $Actions = '';
      if ( !empty($_SESSION['Battle']['Logging']['Actions']) )
        $Actions = pack('l*', ...$_SESSION['Battle']['Logging']['Actions']);

      try
      {
        $PDO->beginTransaction();

        $Update_Battle_Log = $PDO->prepare("
          UPDATE `battle_logs`
          SET `Battle_Duration` = ?, `Actions_Performed` = ?, `Turn_Count` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Battle_Log->execute([
          $_SESSION['Battle']['Last_Action_Time'],
          $Actions,
          $_SESSION['Battle']['Turn_ID'],
          $_SESSION['Battle']['Logging']['Log_ID']
        ]);

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }
    }
  }
