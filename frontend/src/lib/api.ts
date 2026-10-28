export type User = {
  id: number;
  name: string;
  email: string;
};

export type DailyReport = {
  id: number;
  reported_at: string;
  title: string;
  content: string;
  status: "draft" | "submitted";
  user_id: number;
  user: Pick<User, "id" | "name"> | null;
};

export type PaginatedReports = {
  data: DailyReport[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
};

type ApiErrorPayload = {
  message?: string;
  errors?: Record<string, string[]>;
};

export class ApiError extends Error {
  errors: Record<string, string[]>;
  status: number;

  constructor(
    message: string,
    status: number,
    errors: Record<string, string[]> = {},
  ) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.errors = errors;
  }
}

function csrfToken(): string | undefined {
  const token = document.cookie
    .split("; ")
    .find((cookie) => cookie.startsWith("XSRF-TOKEN="))
    ?.slice("XSRF-TOKEN=".length);

  return token ? decodeURIComponent(token) : undefined;
}

export async function apiRequest<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const method = (options.method ?? "GET").toUpperCase();
  const headers = new Headers(options.headers);
  headers.set("Accept", "application/json");

  if (options.body && !headers.has("Content-Type")) {
    headers.set("Content-Type", "application/json");
  }

  if (!["GET", "HEAD", "OPTIONS"].includes(method)) {
    await fetch("/sanctum/csrf-cookie", { credentials: "include" });
    const token = csrfToken();
    if (token) {
      headers.set("X-XSRF-TOKEN", token);
    }
  }

  const response = await fetch(path, {
    ...options,
    method,
    headers,
    credentials: "include",
  });

  if (response.status === 204) {
    return undefined as T;
  }

  const payload = (await response.json().catch(() => ({}))) as ApiErrorPayload;

  if (!response.ok) {
    throw new ApiError(
      payload.message ?? "リクエストに失敗しました。もう一度お試しください。",
      response.status,
      payload.errors,
    );
  }

  return payload as T;
}

export function errorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    const messages = Object.values(error.errors).flat();
    return messages[0] ?? error.message;
  }

  return "通信に失敗しました。時間をおいてもう一度お試しください。";
}
