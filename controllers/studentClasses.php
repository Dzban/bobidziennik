<?php


class StudentClasses extends Controller {

	public function overview()
    {
		$classID = $_GET['id'];
		$class = StudentClass::load($classID);
		$template = Output::i()->renderTemplate('studentClasses', 'overview', [
			'class'=> $class,
			'students'=> $class->getStudents()
		]);
		Output::i()->add($template);
	}

	public function execute() {

	}

    public function edit()
    {
        $id = Request::i()->id;
        if (ctype_digit($id)) {
            $class = StudentClass::load($id);
            $columns = StudentClass::getColumns();
            $defaultValues = [];
            foreach ($columns as $column) {
                $defaultValues[$column] = $class->$column;
            }
            $form = StudentClass::form($defaultValues);
            if ($form->isSuccess()) {
                $values = $form->getValues();
                foreach ($columns as $column) {
                    $class->$column = $values->$column;
                }
                $class->save();
                Output::i()->redirect('?s=studentClasses&do=overview&id='.$id);
            } else {
                Output::i()->add($form);
            }
        } else {
            Output::i()->redirect('?s=studentClasses&do=overview&id='.$id);
        }
    }

	public function add()
    {
		$form = StudentClass::form();
		if ($form->isSuccess()) {
			$fv = $form->getValues();
			$studentClass = new StudentClass([
				'nazwa'=> $fv->nazwa,
				'symbol'=> $fv->symbol,
				'rok'=> $fv->rok,
				'wychowawca'=> $fv->wychowawca
			]);
			$studentClass->_new = true;
			$id = $studentClass->save();
			Output::i()->redirect('?s=studentClasses&do=overview&id='.$id);
		} else {
			$template = Output::i()->renderTemplate('studentClasses', 'add', [
				'form'=> $form
			]);
			Output::i()->add($template);
		}
	}

	public function manage()
	{
		$classes = array_map(function($student) {
			return new StudentClass($student);
		}, DB::i()->select([
			'select'=> '*',
			'from'=> StudentClass::$databaseTable
		]));
		$template = Output::i()->renderTemplate('studentClasses', 'list', [
			'classes'=> $classes
		]);
		Output::i()->add($template);
	}
}
