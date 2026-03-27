declare function route(
    name?: string,
    params?: Record<string, unknown> | unknown[] | string | number | null,
    absolute?: boolean,
): string;
