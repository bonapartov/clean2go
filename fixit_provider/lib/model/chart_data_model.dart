class ChartData {
  final String x;
  final double? y;

  ChartData({required this.x, this.y});

  factory ChartData.fromJson(Map<dynamic, dynamic> json) =>
      ChartData(x: json['x'], y: json['y']?.toDouble());

  Map<dynamic, dynamic> toJson() => {'x': x, 'y': y};
}

class ChartDataColor {
  final String x;
  final double y;
  final dynamic color;

  ChartDataColor(this.x, this.y, this.color);
}
