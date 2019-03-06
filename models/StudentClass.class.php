<?php

require_once('ActiveRecord.class.php');

class StudentClass extends ActiveRecord {
	public static $databaseTable = 'klasa';
	public static $idColumn = 'id';
	public static $columnNames = [
		'id',
		'nazwa',
		'rok',
		'symbol',
		'wychowawca'
	];

	public function getStudents() {
		$columnID = static::$idColumn;
		$students = array_map(function ($studentData) {
			return new Student($studentData);
		}, DB::i()->select([
			'select'=> '*',
			'from'=> Student::$databaseTable,
			'where'=> [
				['id_klasy=?', $this->$columnID]
			]
		]));
		return $students;
	}


	public function leadingTeacher() {
		return Teacher::load($this->wychowawca);
	}

	public function getSubjects() {
		$columnID = static::$idColumn;
		$subjectsIDs = array_map(function($s) {
			return $s['id_przedmiotu'];
		}, DB::i()->select([
			'select'=> 'id_przedmiotu',
			'from'=> 'przypisania',
			'where'=> [
				['id_klasy=?', $this->$columnID]
			]
		]));

		$subjects = DB::i()->select('select * from '.Subject::$databaseTable.' where id in ('.implode(',', $subjectsIDs).')');
		$subjects = DB::i()->select([
			'select'=> '*',
			'from'=> Subject::$databaseTable,
			'where'=> [
				['id in ('.implode(',', array_map(function(){return '?';}, $subjectsIDs)), $subjectsIDs]
			]
		]);

		return $subjects;
	}
	public static function form($defaultValues = null) {
		$teachersModels = array_map(function($teacher) {
			return Teacher::load($teacher['id']);
		}, DB::i()->select([
			'select'=> 'id',
			'from'=> Teacher::$databaseTable
		]));
		$teachers = [];
		foreach ($teachersModels as $teacher) {
			$teachers[$teacher->id] = "{$teacher->imie} {$teacher->nazwisko}";
		}
		$form = new \Nette\Forms\Form();
		$form->addText('nazwa', 'Nazwa')
			->setRequired('Wypełnij pole nazwa.')
			->setHtmlAttribute('placeholder', 'np. Matematyczno-fizyczna');
		$form->addText('symbol', 'Symbol')
			->setRequired('Wypełnij pole Symbol.')
			->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Symbol klasy może mieć maksymalnie 4 litery!', 4)
			->setHtmlAttribute('placeholder', 'np. B2T');
		$form->addText('rok', 'Rok')
			->setHtmlType('number')
			->addRule(\Nette\Forms\Form::INTEGER, 'Rok musi być liczbą.')
			->addRule(\Nette\Forms\Form::RANGE, 'Rok musi być %d-%d.', [1, 4])
			->setRequired('Wypełnij pole rok.');
		$form->addSelect('wychowawca', 'Wychowawca', $teachers);
		$form->addSubmit('send', $defaultValues ? 'Edytuj' : 'Dodaj');
		$form->setDefaults($defaultValues ? $defaultValues : [
			'rok'=> 1
		]);
		return $form;
	}
}
