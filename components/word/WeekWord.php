<?php

namespace app\components\word;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;
use app\models\ScheduleEvents;
use app\models\Casts;
use app\models\UserInSchedule;
use app\models\User;
use app\models\Config;
use app\components\ScheduleComponent;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use app\models\Room;

/**
 *
 * 
 */

class WeekWord extends Model{
    
    /**
     * Генерация недельного расписания в Excel 
     * @param string $from
     * @param string $to
     * @return 
     */
    public static function wordWeekSchedule($from, $to){
        $phpWord = new PhpWord();
       
      $section = $phpWord->addSection(array(
          'orientation' => 'landscape',
          'marginLeft'   => 600,
          'marginRight'  => 600,
          'marginTop'    => 600,
          'marginBottom' => 600,
      ));
       
       $textt = 'drgf hftgdhnfd ghnjdtfgn ygfnjgfn gh mnghmngh nghf ngfn ghfn gh mnghn gh mngh mngh mngh mngh ngh nghn gh ngh nghf ng dfigdfbgdfhjb dfgfdbidfbhjogfib hgfoibhjuogfib dfib dfub oidfub ogfidub ogfiubn oidgfjuboigfjub oigfjuonib ghfoniugf oibnjugfoiubhnj ogfiubjoigfujb oigfubnjoigfubn oigfjubhnoigfujbhoig fuboigfujbnoigfuboniju ';
      $section->addTextBreak(1); // перенос строки
      $section->addText("Table with colspan and rowspan");
       
      $styleTable = array('borderSize' => 6, 'borderColor' => '999999');
      $cellRowSpan = array('vMerge' => 'restart', 'valign' => 'center');
      $cellRowContinue = array('vMerge' => 'continue');
      $cellColSpan2 = array('gridSpan' => 2, 'valign' => 'center');
      $cellColSpan3 = array('gridSpan' => 3, 'valign' => 'center');
       
      $cellHCentered = array('align' => 'center');
      $cellVCentered = array('valign' => 'center');
 
      $phpWord->addTableStyle('Colspan Rowspan', $styleTable);
      $table = $section->addTable('Colspan Rowspan');
      $table->addRow(null, array('tblHeader' => true));
      $table->addCell(2000, $cellVCentered)->addText('A', array('bold' => true), $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('B', array('bold' => true), $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('C', array('bold' => true), $cellHCentered);
      $table->addCell(2000, $cellColSpan2)->addText('D', array('bold' => true), $cellHCentered);
       
      $table->addRow();
      $table->addCell(2000, $cellColSpan3)->addText(' colspan=3 '
              . '(need enough columns under -- one diff from html)', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('E', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('F', null, $cellHCentered);
       
      $table->addRow();
      $table->addCell(2000, $cellRowSpan)->addText('rowspan=2 '
              . '(need one null cell under)', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      $table->addCell(2000, $cellRowSpan)->addText('rowspan=3 '
              . '(nedd 2 null celss under)', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText($textt, null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
       
      $table->addRow();
      $table->addCell(null, $cellRowContinue); // 1 пустая в колонке 1
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      $table->addCell(null, $cellRowContinue); // 1 пустая в колонке 3
      $table->addCell(2000, $cellVCentered)->addText($textt, null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
       
       
      $table->addRow();     
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      $celll = $table->addCell(2000, $cellVCentered)->createTextRun();
      $celll->addText('TTT', ['bold' => true]);
      $celll->addText('KKK', ['color' => '#1C6C80']);
//              ->addText('Т', null, $cellHCentered);
      $table->addCell(null, $cellRowContinue);  // 2 пустая в колонке 3
      $table->addCell(2000, $cellVCentered)->addText($textt, null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
 
       
      $table->addRow();
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText($textt, null, $cellHCentered);
      $table->addCell(2000, $cellVCentered)->addText('Т', null, $cellHCentered);
      
      $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
      $objWriter->save('files/week_schedule/word_test.docx');
      
      return \Yii::$app->response->sendFile('files/week_schedule/word_test.docx');
 

//        $filename = "Расписание_" .$dateFrom ."-" .$dateTo .".xlsx";
//        $writer = new Xlsx($spreadsheet);
//        $writer->save('files/week_schedule/' .$filename);
//        return \Yii::$app->response->sendFile('files/week_schedule/' .$filename);
        
        
    }
    
    public static function minuteToTime($from, $to){
        $result = floor($from / 60) .":" .($from % 60 < 10?"0" .$from % 60:$from % 60);
        if($to){
            $result .= "-" .floor($to / 60) .":" .($to % 60 < 10?"0" .$to % 60:$to % 60);
        }
        return $result;
    }
   
}
