<?php
namespace App\Http\Repositories;
use Illuminate\Database\Eloquent\Model; 
use Throwable; 

class BaseRepository
{
    
	/**
	 * The Model name.
	 *
	 * @var \Illuminate\Database\Eloquent\Model;
	 */
	protected $model;
	
	function __construct($model = null){
	    $this->model = $model;
	}

	/**
	 * Paginate the given query.
	 *
	 * @param The number of models to return for pagination $n integer
	 *
	 * @return mixed
	 */
	public function getPaginate($n, $step = Constante::STEP_PAGINATE)
	{
	    return $this->model::orderBy('created_at','desc')->paginate($step,['*'],'',$n);
	}

	/**
	 * Create a new model and return the instance.
	 *
	 * @param array $inputs
	 *
	 * @return Model instance
	 */
	public function create(array $inputs)
	{
		return $this->model->create($inputs);
	}

	/**
	 * FindOrFail Model and return the instance.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
	 *
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function getById($id)
	{
		return $this->model->findOrFail($id);
	}

	/**
	 * Update the model in the database.
	 *
	 * @param $id
	 * @param array $inputs
	 */
	public function update(array $inputs,$id)
	{
		return $this->find($id)->update($inputs);
	}

	/**
	 * Delete the model from the database.
	 *
	 * @param int $id
	 *
	 * @throws \Throwable
	 */
	public function delete($id)
	{
		return $this->find($id)->delete();
	}
	
	public function getFirstBy($column, $value){
		return $this->model->where($column, $value)->first();
	}

	public function getBy($column,$value){
	    return $this->model->where($column,$value)->get();
	}

	public function getOneBy($column,$value){
	    return $this->model->where($column,$value)->first();
	}

	/**
	 * FindOrFail Model and return the instance.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
	 *
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function find($id)
	{
		return $this->model->findOrFail($id);
	}

	public function all()
	{
	    return $this->model::orderBy('created_at','desc')->get();
	}
}