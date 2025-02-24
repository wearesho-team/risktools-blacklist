<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

/**
 * @link https://doc.blacklist.risktools.pro/#_2
 */
enum Category: string
{
    case MILITARY = 'military'; // військовий
    case CLAIM = 'claim'; // скарга НБУ
    case FRAUD = 'fraud'; // шахрайство
    case CIRCLE = 'circle'; // на прохання близьких осіб (батьки тощо)
    case DEAD = 'dead'; // помер
    case GAMING = 'gaming'; // лудоман
    case INCAPABLE = 'incapable'; // недієздатний
    case WRITEOFF = 'writeoff'; // списання
    case INADEQUATE = 'inadequate'; // неадекватна поведінка
    case ADDICT = 'addict'; // залежність (алгоголізм, наркоманія тощо)
    case LOST_DOCS = 'lost_docs'; // втрачені документи
    case SELF = 'self'; // за власним бажанням
    case OTHER = 'other'; // інше
}
