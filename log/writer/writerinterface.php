<?php

namespace Sugar\Log\Writer;

interface WriterInterface {

	function write($level,$message,array $context=array());

}