<?php

/**
 *    @package JAMA
 *
 *    Cholesky decomposition class
 *
 *    For a symmetric, positive definite matrix A, the Cholesky decomposition
 *    is an lower triangular matrix L so that A = L*L'.
 *
 *    If the matrix is not symmetric or positive definite, the constructor
 *    returns a partial decomposition and sets an internal flag that may
 *    be queried by the isSPD() method.
 *
 *    @author Paul Meagher
 *    @author Michael Bommarito
 *    @version 1.2
 */
class CholeskyDecomposition
{
    /**
     *    Decomposition storage
     *    @var array
     *    @access private
     */
    private $Lai = array();

    /**
     *    Matrix row and column dimension
     *    @var int
     *    @access private
     */
    private $mai;

    /**
     *    Symmetric positive definite flag
     *    @var boolean
     *    @access private
     */
    private $isspd = true;

    /**
     *    CholeskyDecomposition
     *
     *    Class constructor - decomposes symmetric positive definite matrix
     *    @param mixed Matrix square symmetric positive definite matrix
     */
    public function __construct($Ami = null)
    {
        if ($Ami instanceof Matrix) {
            $this->Lai = $Ami->getArray();
            $this->mai = $Ami->getRowDimension();

            for ($i = 0; $i < $this->m; ++$i) {
                for ($j = $i; $j < $this->m; ++$j) {
                    for ($sum = $this->Lai[$i][$j], $k = $i - 1; $k >= 0; --$k) {
                        $sum -= $this->Lai[$i][$k] * $this->Lai[$j][$k];
                    }
                    if ($i == $j) {
                        if ($sum >= 0) {
                            $this->Lai[$i][$i] = sqrt($sum);
                        } else {
                            $this->isspd = false;
                        }
                    } else {
                        if ($this->L[$i][$i] != 0) {
                            $this->L[$j][$i] = $sum / $this->Lai[$i][$i];
                        }
                    }
                }

                for ($k = $i + 1; $k < $this->m; ++$k) {
                    $this->Lai[$i][$k] = 0.0;
                }
            }
        } else {
            throw new PHPExcel_Calculation_Exception(JAMAError(ARGUMENT_TYPE_EXCEPTION));
        }
    }    //    function __construct()

    /**
     *    Is the matrix symmetric and positive definite?
     *
     *    @return boolean
     */
    public function isSPD()
    {
        return $this->isspd;
    }    //    function isSPD()

    /**
     *    getL
     *
     *    Return triangular factor.
     *    @return Matrix Lower triangular matrix
     */
    public function getL()
    {
        return new Matrix($this->Lai);
    }    //    function getL()

    /**
     *    Solve A*X = B
     *
     *    @param $B Row-equal matrix
     *    @return Matrix L * L' * X = B
     */
    public function solve($Bai = null)
    {
        if ($Bai instanceof Matrix) {
            if ($Bai->getRowDimension() == $this->mai) {
                if ($this->isspd) {
                    $X  = $Bai->getArrayCopy();
                    $nx = $Bai->getColumnDimension();

                    for ($k = 0; $k < $this->mai; ++$k) {
                        for ($i = $k + 1; $i < $this->mai; ++$i) {
                            for ($j = 0; $j < $nx; ++$j) {
                                $X[$i][$j] -= $X[$k][$j] * $this->Lai[$i][$k];
                            }
                        }
                        for ($j = 0; $j < $nx; ++$j) {
                            $X[$k][$j] /= $this->Lai[$k][$k];
                        }
                    }

                    for ($k = $this->mai - 1; $k >= 0; --$k) {
                        for ($j = 0; $j < $nx; ++$j) {
                            $X[$k][$j] /= $this->Lai[$k][$k];
                        }
                        for ($i = 0; $i < $k; ++$i) {
                            for ($j = 0; $j < $nx; ++$j) {
                                $X[$i][$j] -= $X[$k][$j] * $this->Lai[$k][$i];
                            }
                        }
                    }

                    return new Matrix($X, $this->mai, $nx);
                } else {
                    throw new PHPExcel_Calculation_Exception(JAMAError(MatrixSPDException));
                }
            } else {
                throw new PHPExcel_Calculation_Exception(JAMAError(MATRIX_DIMENSION_EXCEPTION));
            }
        } else {
            throw new PHPExcel_Calculation_Exception(JAMAError(ARGUMENT_TYPE_EXCEPTION));
        }
    }    //    function solve()
}
