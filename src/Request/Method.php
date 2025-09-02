<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Request;

enum Method: string
{

	case Get = 'GET';

	case Post = 'POST';

	case Head = 'HEAD';

	case Put = 'PUT';

	case Delete = 'DELETE';

	case Patch = 'PATCH';

	case Options = 'OPTIONS';

}