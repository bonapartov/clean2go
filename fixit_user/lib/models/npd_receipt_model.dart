// Receipt of a self-employed executor for a booking («Мой налог», ROADMAP
// Sprint 8) — `npd_receipts` of the booking API (App\Support\Npd\NpdReceipts::
// forClient). By law the self-employed must hand it to the buyer. Own code.
class NpdReceiptModel {
  final String executorName;
  final String executorInn;
  final double amount;
  final String statusLabel;
  final String? number;
  final String? url;
  final bool isTest;

  NpdReceiptModel.fromJson(Map<String, dynamic> j)
      : executorName = '${j['executor_name'] ?? ''}',
        executorInn = '${j['executor_inn'] ?? ''}',
        amount = double.tryParse('${j['amount'] ?? 0}') ?? 0,
        statusLabel = '${j['status_label'] ?? ''}',
        number = j['number'],
        url = j['url'],
        isTest = j['is_test'] == true;
}
