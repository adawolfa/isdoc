<?php declare(strict_types=1);

namespace Adawolfa\ISDOC;

/**
 * ISDOC file format for Reader::file()/Writer::file(); pass null (the default) to auto-detect from the extension.
 */
enum Format: string
{

	case ISDOC = 'isdoc';
	case ISDOCX = 'isdocx';
	case PDF = 'pdf';

}