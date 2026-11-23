<?php

abstract class Field {
    public function __construct(protected string $name) {}

    abstract public render(): string;
}