import DailyReportForm from "@/components/DailyReportForm";

export default async function EditReportPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  return <DailyReportForm reportId={id} />;
}
