import 'package:flutter/material.dart';

class CategoriesGridList extends StatelessWidget {
  final dynamic data;
  final dynamic index;
  final dynamic list;
  final dynamic title;
  final dynamic status;
  final dynamic style;
  final dynamic styleTitle;
  final dynamic onTap;
  final dynamic selectService;
  final dynamic isSetting;
  final dynamic document;
  final dynamic isSentByMe;
  final dynamic isMe;
  final dynamic isOffer;
  final dynamic isEmailOrPhone;
  final dynamic faqList;
  final dynamic bookingId;
  final dynamic bookingData;
  final dynamic serviceId;
  final dynamic isUpdate;
  final dynamic color;
  final dynamic child;

  const CategoriesGridList({super.key,
    this.data,
    this.index,
    this.list,
    this.title,
    this.status,
    this.style,
    this.styleTitle,
    this.onTap,
    this.selectService,
    this.isSetting,
    this.document,
    this.isSentByMe,
    this.isMe,
    this.isOffer,
    this.isEmailOrPhone,
    this.faqList,
    this.bookingId,
    this.bookingData,
    this.serviceId,
    this.isUpdate,
    this.color,
    this.child,
  });

  @override
  Widget build(BuildContext context) {
    return const SizedBox.shrink();
  }
}
