<?php

namespace Libmf;

// TODO use enum when PHP 8.0 reaches EOL
class Loss
{
    public const RealL2 = 0;
    public const RealL1 = 1;
    public const RealKL = 2;
    public const BinaryLog = 5;
    public const BinaryL2 = 6;
    public const BinaryL1 = 7;
    public const OneClassRow = 10;
    public const OneClassCol = 11;
    public const OneClassL2 = 12;
}
