import React from "react";

function currencyFormatter({
  amount,
  currency = "USD",
  locale,
}: {
  amount: number;
  currency?: string;
  locale?: string;
}) {
  return new Intl.NumberFormat(locale, { style: "currency", currency }).format(
    amount
  );
}

export default currencyFormatter;
