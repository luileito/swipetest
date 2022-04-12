#!/usr/bin/env bash
#
# Extract and/or translate strings from PHP source code.
# Example: bash translate.sh es_ES

locale="$1"
domain="messages"

# Ensure that a locale is provided.
if [ -z "$locale" ]; then
  echo "No locale specified. Please run: $0 lang_country"
  echo
  echo "Examples:"
  echo "* For Spanish, run: $0 es_ES"
  echo "* For Finnish, run: $0 fi_FI"
  exit 1
fi

# Ensure that we have the locale installed.
if [ -z "$(locale -a | grep $locale)" ]; then
  echo "Locale $locale not found."
  echo "Please run: sudo locale-gen $locale.utf8 && sudo update-locale";
  exit 1;
fi

# Extract translatable strings from source code.
# This will create a file named `$domain.po`.
find . -name "*.php" | xargs xgettext --from-code 'UTF-8' --no-wrap -k'_' -k'_e' -k'_x' -d "$domain"

# Ensure locale dir exists.
locale_dir="locale/$locale"
if [ ! -d $locale_dir ]; then
  mkdir $locale_dir
fi

# Merge previous translations (if any) with the new ones.
curr_messages="$locale_dir/$domain.po"
prev_messages="$locale_dir/$domain.prev.po"
if [ -f "$curr_messages" ]; then
  cp "$curr_messages" "$prev_messages"
  msgmerge -U -N "$prev_messages" "$domain.po"
  mv "$prev_messages" "$curr_messages"
  rm "$domain.po"
else
  mv "$domain.po" "$curr_messages"
fi

# Update default charset and fill in the locale language.
sed -i -e 's|CHARSET|utf-8|g' -e 's|"Language: \\n"|"Language: '$locale'\\n"|' $curr_messages

# Ensure domain dir exists.
lc_msg_dir="$locale_dir/LC_MESSAGES/"
if [ ! -d $lc_msg_dir ]; then
  mkdir $lc_msg_dir
fi

# Finally convert the PO file (plain text) in MO file (binary).
msgfmt "$curr_messages" -o "$lc_msg_dir/$domain.mo"
