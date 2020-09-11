<?php


namespace lu\pata\jain;

use Exception;
use Sop\ASN1\Element;
use Sop\ASN1\Type\Primitive\BitString;
use Sop\ASN1\Type\UnspecifiedType;

class Key
{
    public function create($data){
        $der=hex2bin($data);
        $seq = UnspecifiedType::fromDER($der)->asSequence();

        if(!$seq->at(0)->isType(Element::TYPE_SEQUENCE)) throw new Exception('Invalid raw key provided.');

        $identification=$seq->at(0)->asSequence();

        if(!$identification->at(0)->isType(Element::TYPE_OBJECT_IDENTIFIER)) throw new Exception('Invalid raw key provided, no OID record where one expected.');
        $oid=$identification->at(0)->asObjectIdentifier();

        if($oid->oid()=='1.2.840.113549.1.1.1') $this->createRSA($seq->at(1)->asBitString());
    }

    private function createRSA(BitString $kv){
        $vseq=$kv->range(0,$kv->numBits());
        echo $vseq;

    }
}