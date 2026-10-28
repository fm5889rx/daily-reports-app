"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import {
  ApiError,
  apiRequest,
  DailyReport,
  errorMessage,
  PaginatedReports,
  User,
} from "@/lib/api";

export default function DailyReportsPage() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [reports, setReports] = useState<DailyReport[]>([]);
  const [page, setPage] = useState(1);
  const [refreshKey, setRefreshKey] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [deletingId, setDeletingId] = useState<number | null>(null);

  useEffect(() => {
    let active = true;

    async function loadPage() {
      try {
        const currentUser = await apiRequest<User>("/api/user");
        const response = await apiRequest<PaginatedReports>(
          `/api/daily-reports?page=${page}`,
        );
        if (!active) return;
        setUser(currentUser);
        setReports(response.data);
        setPage(response.meta.current_page);
        setLastPage(response.meta.last_page);
        setTotal(response.meta.total);
      } catch (requestError) {
        if (!active) return;
        if (requestError instanceof ApiError && requestError.status === 401) {
          router.replace("/login");
        } else {
          setError(errorMessage(requestError));
        }
      } finally {
        if (active) setLoading(false);
      }
    }

    void loadPage();
    return () => {
      active = false;
    };
  }, [page, refreshKey, router]);

  async function logout() {
    try {
      await apiRequest<void>("/auth/logout", { method: "POST" });
      router.replace("/login");
      router.refresh();
    } catch (requestError) {
      setError(errorMessage(requestError));
    }
  }

  function goToPage(pageNumber: number) {
    setError("");
    setLoading(true);
    setPage(pageNumber);
  }

  async function deleteReport(report: DailyReport) {
    if (!window.confirm(`「${report.title}」を削除しますか？`)) {
      return;
    }

    setDeletingId(report.id);
    try {
      await apiRequest<void>(`/api/daily-reports/${report.id}`, {
        method: "DELETE",
      });
      const nextPage = reports.length === 1 && page > 1 ? page - 1 : page;
      if (nextPage !== page) {
        goToPage(nextPage);
      } else {
        setLoading(true);
        setError("");
        setRefreshKey((current) => current + 1);
      }
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setDeletingId(null);
    }
  }

  const firstItem = total === 0 ? 0 : (page - 1) * 5 + 1;
  const lastItem = Math.min(page * 5, total);

  return (
    <main className="app-shell">
      <header className="topbar">
        <div className="topbar-inner">
          <Link href="/daily-reports" className="brand">
            <span className="brand-mark">D</span>
            <span>daily<span className="brand-light">log</span></span>
          </Link>
          <div className="topbar-user">
            <span className="avatar">{user?.name.slice(0, 1) ?? "?"}</span>
            <span className="user-name">{user?.name ?? "読み込み中"}</span>
            <button className="text-button" onClick={logout}>ログアウト</button>
          </div>
        </div>
      </header>

      <section className="content-wrap">
        <div className="page-heading">
          <div>
            <p className="eyebrow">YOUR TEAM, IN THE LOOP</p>
            <h1>日報一覧</h1>
            <p className="page-subtitle">チームの日々の記録を確認できます。</p>
          </div>
          <Link className="button button-primary" href="/daily-reports/create">
            <span className="plus-icon" aria-hidden="true">＋</span>
            日報を作成
          </Link>
        </div>

        <div className="list-toolbar">
          <div>
            <h2>すべての日報 <span className="count-badge">{total}</span></h2>
            <p>新しい日報から順に表示しています</p>
          </div>
          <span className="page-count">
            {loading ? "読み込み中..." : `${firstItem}–${lastItem} 件 / 全 ${total} 件`}
          </span>
        </div>

        {error && <div className="notice-error" role="alert">{error}</div>}

        <section className="report-list" aria-label="日報一覧">
          {loading ? (
            <div className="empty-state"><span className="spinner" />日報を読み込んでいます...</div>
          ) : reports.length === 0 ? (
            <div className="empty-state">
              <span className="empty-icon">✳</span>
              <h3>日報はまだありません</h3>
              <p>最初の日報を作成して、チームに共有しましょう。</p>
              <Link className="button button-primary" href="/daily-reports/create">日報を作成</Link>
            </div>
          ) : (
            reports.map((report) => (
              <article className="report-card" key={report.id}>
                <div className="report-date">
                  <span>{formatDate(report.reported_at).month}</span>
                  <strong>{formatDate(report.reported_at).day}</strong>
                  <span>{formatDate(report.reported_at).weekday}</span>
                </div>
                <div className="report-main">
                  <div className="report-title-line">
                    <h3>{report.title}</h3>
                    <span className={`status-pill status-${report.status}`}>
                      <span className="status-dot" />
                      {report.status === "submitted" ? "提出済" : "下書き"}
                    </span>
                  </div>
                  <p className="report-content">{report.content}</p>
                  <div className="report-meta">
                    <span className="mini-avatar">{report.user?.name.slice(0, 1) ?? "?"}</span>
                    <span>{report.user?.name ?? "不明なユーザー"}</span>
                  </div>
                </div>
                {report.user_id === user?.id && (
                  <div className="report-actions">
                    <Link
                      className="icon-button"
                      href={`/daily-reports/${report.id}/edit`}
                      aria-label={`${report.title}を編集`}
                      title="編集"
                    >↗</Link>
                    <button
                      className="icon-button delete-button"
                      onClick={() => void deleteReport(report)}
                      disabled={deletingId === report.id}
                      aria-label={`${report.title}を削除`}
                      title="削除"
                    >⌫</button>
                  </div>
                )}
              </article>
            ))
          )}
        </section>

        {lastPage > 1 && (
          <nav className="pagination" aria-label="ページネーション">
            <button
              className="page-button page-arrow"
              onClick={() => goToPage(Math.max(1, page - 1))}
              disabled={page === 1 || loading}
              aria-label="前のページ"
            >←</button>
            {Array.from({ length: lastPage }, (_, index) => index + 1).map((pageNumber) => (
              <button
                className={`page-button ${pageNumber === page ? "page-active" : ""}`}
                key={pageNumber}
                onClick={() => goToPage(pageNumber)}
                disabled={loading}
                aria-current={pageNumber === page ? "page" : undefined}
              >{pageNumber}</button>
            ))}
            <button
              className="page-button page-arrow"
              onClick={() => goToPage(Math.min(lastPage, page + 1))}
              disabled={page === lastPage || loading}
              aria-label="次のページ"
            >→</button>
          </nav>
        )}

        <footer className="page-footer">毎日の積み重ねが、チームの力になる。</footer>
      </section>
    </main>
  );
}

function formatDate(value: string) {
  const date = new Date(`${value}T00:00:00`);
  return {
    month: new Intl.DateTimeFormat("ja-JP", { month: "short" }).format(date),
    day: new Intl.DateTimeFormat("ja-JP", { day: "2-digit" }).format(date),
    weekday: new Intl.DateTimeFormat("ja-JP", { weekday: "short" }).format(date),
  };
}
