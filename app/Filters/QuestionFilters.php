<?php

namespace App\Filters;

class QuestionFilters extends QueryFilter
{
    public function search($term = "")
    {
        return $this->builder->where(function ($query) use ($term) {
            $query->where('question', 'like', '%' . $term . '%')
                  ->orWhere('code', 'like', '%' . $term . '%');
        });
    }

    public function type($id = null)
    {
        return $this->builder->where('question_type_id', $id);
    }

    public function skill($id = null)
    {
        return $this->builder->where('skill_id', $id);
    }

    public function topic($id = null)
    {
        return $this->builder->where('topic_id', $id);
    }

    public function level($id = null)
    {
        return $this->builder->where('difficulty_level_id', $id);
    }

    public function status($status = null)
    {
        if ($status === 'active') {
            return $this->builder->where('is_active', 1);
        } elseif ($status === 'pending') {
            return $this->builder->where('is_active', 0);
        }
        return $this->builder;
    }
}
