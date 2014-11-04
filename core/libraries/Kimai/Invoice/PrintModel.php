<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) Kimai-Development-Team
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A data model holding everything to render an invoice.
 *
 * @author Kevin Papst
 */
class Kimai_Invoice_PrintModel
{

    /**
     * @var array
     */
    private $entries = array();
    /**
     * @var array
     */
    private $disbursements = array();
    /**
     * @var int
     */
    private $amount = 0;
    /**
     * @var array
     */
    private $customer = array();
    /**
     * @var int
     */
    private $GST = 0;
    /**
     * @var int
     */
    private $GSTRate = 0;
    /**
     * @var int
     */
    private $total = 0;
    /**
     * @var array
     */
    private $projects = array();
    /**
     * @var string
     */
    private $invoiceId = '';
    /**
     * @var int
     */
    private $beginDate = 0;
    /**
     * @var string
     */
    private $shortBeginEndDateRange = '';
    /**
     * @var int
     */
    private $endDate = 0;
    /**
     * @var int
     */
    private $invoiceDate = 0;
    /**
     * @var string
     */
    private $dateFormat = '%d.%m.%Y';
    /**
     * @var int
     */
    private $dueDate = 0;
    /**
     * @var string
     */
    private $currencySign = '$';
    /**
     * @var string
     */
    private $currencyName = 'EUR';

    /**
     * Returns all interval values as array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'entries'       => $this->getEntries(),     // array
            'disbursements'       => $this->getDisbursements(),     // array
            'amount'        => $this->getAmount(),
            'customer'      => $this->getCustomer(),    // array
            'GST'           => $this->getGST(),
            'GSTRate'       => $this->getGSTRate(),
            'total'         => $this->getTotal(),
            'projects'      => $this->getProjects(),    // array
            'invoiceId'     => $this->getInvoiceId(),
            'beginDate'     => $this->getBeginDate(),
            'endDate'       => $this->getEndDate(),
            'shortBeginEndDateRange' => $this->getShortBeginEndDateRange(),
            'invoiceDate'   => $this->getInvoiceDate(),
            'dateFormat'    => $this->getDateFormat(),
            'dueDate'       => $this->getDueDate(),
            'currencySign'  => $this->getCurrencySign(),
            'currencyName'  => $this->getCurrencyName()
                
                
                
        );
    }

    /**
     * @param string $currencyName
     */
    public function setCurrencyName($currencyName)
    {
        $this->currencyName = $currencyName;
    }

    /**
     * @return string
     */
    public function getCurrencyName()
    {
        return $this->currencyName;
    }

    /**
     * @param string $currencySign
     */
    public function setCurrencySign($currencySign)
    {
        $this->currencySign = $currencySign;
    }

    /**
     * @return string
     */
    public function getCurrencySign()
    {
        return $this->currencySign;
    }

    
    public function getShortBeginEndDateRange() {
      return $this->shortBeginEndDateRange;
    }
    
    public function setShortBeginEndDateRange($value) {
      $this->shortBeginEndDateRange = $value;
    }
    
    /**
     * @param int $beginDate
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;
    }

    /**
     * @return int
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param int $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return int
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param int $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return int
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param int $invoiceDate
     */
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;
    }

    /**
     * @return int
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * @param string $invoiceId
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param array $entries
     */
    public function setEntries(array $entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param array $disbursements
     */
    public function setDisbursements(array $disbursements)
    {
        $this->disbursements = $disbursements;
    }

    /**
     * @return array
     */
    public function getDisbursements()
    {
        return $this->disbursements;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param array $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return array
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param int $GST
     */
    public function setGST($GST)
    {
        $this->GST = $GST;
    }

    /**
     * @return int
     */
    public function getGST()
    {
        return $this->GST;
    }

    /**
     * @param int $GSTRate
     */
    public function setGSTRate($GSTRate)
    {
        $this->GSTRate = $GSTRate;
    }

    /**
     * @return int
     */
    public function getGSTRate()
    {
        return $this->GSTRate;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param array $projects
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;
    }

    /**
     * @return array
     */
    public function getProjects()
    {
        return $this->projects;
    }


}