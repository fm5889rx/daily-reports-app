"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";
import { apiRequest, errorMessage } from "@/lib/api";

type AuthFormProps = {
  mode: "login" | "register";
};

export default function AuthForm({ mode }: AuthFormProps) {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const registering = mode === "register";

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");
    setSubmitting(true);

    try {
      await apiRequest(registering ? "/auth/register" : "/auth/login", {
        method: "POST",
        body: JSON.stringify({
          email,
          password,
          password_confirmation: passwordConfirmation,
        }),
      });
      router.replace("/daily-reports");
      router.refresh();
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="auth-page">
      <section className="auth-card">
        <Link className="brand auth-brand" href="/login" aria-label="Daily Log">
          <span className="brand-mark">D</span>
          <span>daily<span className="brand-light">log</span></span>
        </Link>
        <div className="auth-heading">
          <p className="eyebrow">DAILY REPORTS, MADE SIMPLE</p>
          <h1>{registering ? "アカウントを作成" : "おかえりなさい"}</h1>
          <p>{registering ? "日報管理を、ここから始めましょう。" : "日報を確認するにはログインしてください。"}</p>
        </div>
        <form className="form-stack" onSubmit={submit}>
          <label className="field">
            <span>メールアドレス</span>
            <input
              type="email"
              autoComplete="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="name@example.com"
              required
            />
          </label>
          <label className="field">
            <span>パスワード</span>
            <input
              type="password"
              autoComplete={registering ? "new-password" : "current-password"}
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder={registering ? "8文字以上" : "パスワードを入力"}
              minLength={registering ? 8 : undefined}
              required
            />
          </label>
          <label className="field">
            <span>パスワード確認</span>
            <input
              type="password"
              autoComplete="new-password"
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              placeholder="もう一度入力"
              required
            />
          </label>
          {error && <p className="form-error" role="alert">{error}</p>}
          <button className="button button-primary button-full" disabled={submitting}>
            {submitting ? "処理中..." : registering ? "アカウントを作成" : "ログイン"}
            <span aria-hidden="true">→</span>
          </button>
        </form>
        <p className="auth-switch">
          {registering ? "すでにアカウントをお持ちですか？" : "アカウントをお持ちでないですか？"}{" "}
          <Link href={registering ? "/login" : "/register"}>
            {registering ? "ログイン" : "新規登録"}
          </Link>
        </p>
      </section>
      <p className="auth-footer">シンプルに書いて、チームで共有。</p>
    </main>
  );
}
