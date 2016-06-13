<?php
class GradeCourseModel extends CommonModel{

    public function getClassInfo($sId, $cType, $cGrade, $csType, $allSchool) {

        // �õ�ѧ��
        $types = $allSchool[$sId][s_type];
        $types = explode(',', $types);
        foreach ($csType as $csKey => $csVa){
            if (!in_array($csKey, $types)) {
                unset($csType[$csKey]);
            }
        }
        $classInfo['csType'] = $csType;

        // �õ��꼶
        $grade = C('GRADE_TYPE');
        $grade = $grade[$cType];
        $classInfo['grade'] = $grade;
        return $classInfo;
    }

}


?>

