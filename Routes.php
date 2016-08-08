<?php

namespace Rest;

interface Routes
{
    public function get();
    public function post();
    public function put();
    public function delete();
    public function error();
}
