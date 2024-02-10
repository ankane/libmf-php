<?php

namespace Libmf;

enum Loss : int
{
    case RealL2 = 0;
    case RealL1 = 1;
    case RealKL = 2;
    case BinaryLog = 5;
    case BinaryL2 = 6;
    case BinaryL1 = 7;
    case OneClassRow = 10;
    case OneClassCol = 11;
    case OneClassL2 = 12;
}
