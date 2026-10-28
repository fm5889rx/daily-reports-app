"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { FormEvent, useEffect, useState } from "react";
import { ApiError, apiRequest, DailyReport, errorMessage } from "@/lib/api";

type DailyReportFormProps = {
  reportId?: string;
};

type ReportResponse = {
  data: DailyReport;
};

type ReportValues = {
  reported_at: string;
  title: string;
  content: string;
  status: "draft" | "submitted";
};

const initialValues: ReportValues = {
  reported_at: "",
  title: "",
  content: "",
  status: "draft",
};

export default function DailyReportForm({ reportId }: DailyReportFormProps) {
  const router = useRouter();
  const editing = Boolean(reportId);
  const [values, setValues] = useState<ReportValues>(initialValues);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(editing);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!reportId) {
      const frame = window.requestAnimationFrame(() => {
        setValues((current) => ({
          ...current,
          reported_at: new Date().toLocaleDateString("sv-SE"),
        }));
      });

      return () => window.cancelAnimationFrame(frame);
    }

    apiRequest<ReportResponse>(`/api/daily-reports/${reportId}`)
      .then(({ data }) => {
        setValues({
          reported_at: data.reported_at,
          title: data.title,
          content: data.content,
          status: data.status,
        });
      })
      .catch((requestError: unknown) => {
        if (requestError instanceof ApiError && requestError.status === 401) {
          router.replace("/login");
          return;
        }
        setError(errorMessage(requestError));
      })
      .finally(() => setLoading(false));
  }, [reportId, router]);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");
    setSubmitting(true);

    try {
      await apiRequest(
        editing ? `/api/daily-reports/${reportId}` : "/api/daily-reports",
        {
          method: editing ? "PUT" : "POST",
          body: JSON.stringify(values),
        },
      );
      router.push("/daily-reports");
      router.refresh();
    } catch (requestError) {
      if (requestError instanceof ApiError && requestError.status === 401) {
        router.replace("/login");
      } else {
        setError(errorMessage(requestError));
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (loading) {
    return <main className="form-page"><div className="empty-state"><span className="spinner" />日報を読み込んでいます...</div></main>;
  }

  return (
    <main className="app-shell">
      <header className="topbar">
        <div className="topbar-inner">
          <Link href="/daily-reports" className="brand">
            <span className="brand-mark">D</span>
            <span>daily<span className="brand-light">log</span></span>
          </Link>
          <Link className="text-button" href="/daily-reports">一覧へ戻る</Link>
        </div>
      </header>
      <section className="form-page">
        <Link className="back-link" href="/daily-reports">← 日報一覧</Link>
        <div className="form-heading">
          <p className="eyebrow">{editing ? "UPDATE YOUR ENTRY" : "CAPTURE YOUR DAY"}</p>
          <h1>{editing ? "日報を編集" : "日報を作成"}</h1>
          <p>{editing ? "内容を更新して、チームに共有しましょう。" : "今日の取り組みや気づきを記録しましょう。"}</p>
        </div>
        <form className="report-form" onSubmit={submit}>
          <label className="field">
            <span>報告日 <b>*</b></span>
            <input
              type="date"
              value={values.reported_at}
              onChange={(event) => setValues({ ...values, reported_at: event.target.value })}
              required
            />
          </label>
          <label className="field">
            <span>タイトル <b>*</b></span>
            <input
              type="text"
              value={values.title}
              maxLength={50}
              onChange={(event) => setValues({ ...values, title: event.target.value })}
              placeholder="例：新機能の実装を完了しました"
              required
            />
            <small>{values.title.length} / 50文字</small>
          </label>
          <label className="field">
            <span>日報の内容 <b>*</b></span>
            <textarea
              value={values.content}
              minLength={10}
              rows={8}
              onChange={(event) => setValues({ ...values, content: event.target.value })}
              placeholder={"今日取り組んだこと、進捗、気づいたことなどを書いてみましょう。\n\n・完了したタスク\n・困っていること、相談したいこと\n・明日の予定"}
              required
            />
            <small>10文字以上で入力してください。</small>
          </label>
          <fieldset className="field status-field">
            <legend>ステータス</legend>
            <label className={`status-option ${values.status === "draft" ? "option-selected" : ""}`}>
              <input
                type="radio"
                name="status"
                value="draft"
                checked={values.status === "draft"}
                onChange={() => setValues({ ...values, status: "draft" })}
              />
              <span className="option-indicator" />
              <span><strong>下書き</strong><small>後から編集できます</small></span>
            </label>
            <label className={`status-option ${values.status === "submitted" ? "option-selected" : ""}`}>
              <input
                type="radio"
                name="status"
                value="submitted"
                checked={values.status === "submitted"}
                onChange={() => setValues({ ...values, status: "submitted" })}
              />
              <span className="option-indicator" />
              <span><strong>提出済</strong><small>チームに共有します</small></span>
            </label>
          </fieldset>
          {error && <p className="form-error" role="alert">{error}</p>}
          <div className="form-actions">
            <Link className="button button-secondary" href="/daily-reports">キャンセル</Link>
            <button className="button button-primary" disabled={submitting}>
              {submitting ? "保存中..." : editing ? "変更を保存" : "日報を作成"}
              <span aria-hidden="true">→</span>
            </button>
          </div>
        </form>
      </section>
    </main>
  );
}
